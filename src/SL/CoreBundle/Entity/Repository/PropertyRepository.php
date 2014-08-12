<?php

namespace SL\CoreBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use SL\CoreBundle\Entity\EntityClass\EntityClass;

/**
 * PropertyRepository
 *
 */
class PropertyRepository extends EntityRepository
{
	/**
	 * Select a property by $criteria
	 *
	 * @param array $criteria Associated array with : 
	 *	- integer entityClass An entityClass id
	 *	- string displayName Display name of Property
	 *
	 * @return array
	 */
	public function findByEntityClassAndDisplayName($criteria)
	{
		$qb = $this	->getEntityManager()
			 		->getRepository('SLCoreBundle:EntityClass\Property')->createQueryBuilder('p')
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
	 * @param SL\CoreBundle\Entity\EntityClass\EntityClass $entityClass 
	 *
	 * @return array
	 */
  	public function findPropertyEntityByEntityClass(EntityClass $entityClass){

		$qb = $this ->createQueryBuilder('p')
					->join('p.entityClass', 'o')
					->where('p INSTANCE OF SLCoreBundle:EntityClass\PropertyEntity')
					->andWhere('o.id = :id')
					->setParameter('id', $entityClass->getId());

		return $qb->getQuery()
	              ->getResult();
	}
}

