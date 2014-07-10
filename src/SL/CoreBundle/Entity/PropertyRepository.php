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
     *
     * @return QueryBuilder $qb 
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
	 * Select max Property display order of an Object
	 *
	 * @param Object $object Parent Object 
	 *
	 * @return Integer Max Property display order of an Object 
	*/
	public function findMaxDisplayOrder(Object $object)
  	{
	    $qb = $this ->createQueryBuilder('p')
	               	->select('MAX(p.displayOrder)')
	               	->where('p.object = :object')
	               	->setParameter('object', $object);

	    return $qb 	->getQuery()
	              	->getSingleScalarResult();
	}

	/**
	 * Select a Property by Object
	 *
	 * @param Array $criteria Associated array with : 
	 *	- Integer object An object id
	 *	- String displayName The display name of Property
	 *
	 * @return Collection  A collection of selected Property
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
	 * Select enabled Property by Object
	 *
	 * @param Object $object Object
	 *
	 * @return Collection  A collection of enabled selected Property
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
}

