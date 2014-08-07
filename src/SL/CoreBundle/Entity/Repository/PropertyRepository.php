<?php

namespace SL\CoreBundle\Entity\Repository;

//Doctrine classes
use Doctrine\ORM\EntityRepository;

//Custom classes
use SL\CoreBundle\Entity\EntityClass;

/**
 * PropertyRepository
 *
 */
class PropertyRepository extends EntityRepository
{
	/**
	 * Select a property by entityClass
	 *
	 * @param Array $criteria Associated array with : 
	 *	- Integer entityClass An entityClass id
	 *	- String displayName The display name of Property
	 */
	public function findByEntityClassAndDisplayName($criteria)
	{
		$qb = $this	->getEntityManager()
			 		->getRepository('SLCoreBundle:Property')->createQueryBuilder('p')
					->select('p')
		           	->join('p.entityClass','o')
		           	->where('o.id = :entityClassId')
		           	->setParameter('entityClassId', $criteria['entityClass'])
		           	->andWhere('p.displayName = :displayName')
		           	->setParameter('displayName', $criteria['displayName']);

	    return $qb->getQuery()
	              ->getResult();
	}

  	/**
	 * Select only entity property of an entityClass
	 *
	 * @param EntityClass $entityClass 
	 */
  	public function findEntityPropertyByEntityClass(EntityClass $entityClass){

		$qb = $this ->createQueryBuilder('p')
					->join('p.entityClass', 'o')
					->where('p INSTANCE OF SLCoreBundle:EntityProperty')
					->andWhere('o.id = :id')
					->setParameter('id', $entityClass->getId());

		return $qb->getQuery()
	              ->getResult();
	}
}
