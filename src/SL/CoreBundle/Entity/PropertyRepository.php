<?php

namespace SL\CoreBundle\Entity;

//Doctrine classes
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder; 

//Custom classes
use SL\CoreBundle\Entity\Object;

/**
 * PropertyRepository
 *
 */
class PropertyRepository extends EntityRepository
{
	/**
     * Generic join and select
     *
     * @param QueryBuilder $qb 
     */
	public function baseJoin(QueryBuilder $qb)
	{
	    $qb ->join('p.fieldType', 'ft')
	       	->join('ft.fieldCategory', 'fg')
		    ->join('p.object', 'po')
		    ->leftjoin('p.targetObject', 'to')
		    ->addSelect('p, fg, ft, po, to');
	       
	    return $qb;
	}

	/**
	 * Select a property by object
	 *
	 * @param Array $criteria Associated array with : 
	 *	- Integer object An object id
	 *	- String displayName The display name of Property
	 */
	public function findByObjectAndDisplayName($criteria)
	{
		$qb = $this	->getEntityManager()
			 		->getRepository('SLCoreBundle:Property')->createQueryBuilder('p')
					->select('p')
		           	->join('p.object','o')
		           	->where('o.id = :objectId')
		           	->setParameter('objectId', $criteria['object'])
		           	->andWhere('p.displayName = :displayName')
		           	->setParameter('displayName', $criteria['displayName']);

	    return $qb->getQuery()
	              ->getResult();
	}

	/**
	 * Select enabled property by object
	 *
	 * @param Object $object 
	 */
	public function findEnabledByObject(Object $object){
    
	    $qb = $this ->createQueryBuilder('p')
	                ->leftJoin('p.fieldType', 'ft')
	                ->leftJoin('p.object', 'o')
	                ->where('o.id = :id')
	                ->setParameter('id', $object->getId())
	                ->andWhere('p.isEnabled = true')
	                ->addSelect('ft')
	                ->orderBy('p.displayOrder', 'ASC');

	    return $qb->getQuery()
	              ->getResult();
  	}

  	/**
	 * Select only entity property of an object
	 *
	 * @param Object $object 
	 */
  	public function findEntityPropertyByObject(Object $object){

		$qb = $this ->createQueryBuilder('p')
					->join('p.object', 'o')
					->where('p INSTANCE OF SLCoreBundle:EntityProperty')
					->andWhere('o.id = :id')
					->setParameter('id', $object->getId());

		return $qb->getQuery()
	              ->getResult();
	}
}

