<?php

namespace SL\CoreBundle\Entity\Repository;

use Gedmo\Loggable\Entity\Repository\LogEntryRepository as BaseLogEntryRepository;
use Gedmo\Tool\Wrapper\EntityWrapper;

use SL\CoreBundle\Entity\MappedSuperclass\DataAbstractEntity;

class LogEntryRepository extends BaseLogEntryRepository
{
    /**
     * Find current version for $entity
     *
     * @param DataAbstractEntity $entity
     *
     * @return array
     */
	public function findCurrentVersion(DataAbstractEntity $entity){

		$wrapped = new EntityWrapper($entity, $this->_em);
        $objectClass = $wrapped->getMetadata()->name;
        $objectId = $wrapped->getIdentifier();

		$qb = $this	->createQueryBuilder('le')
					->where('le.objectClass = :objectClass')
					->setParameter('objectClass', $objectClass)
					->andWhere('le.objectId = :objectId')
					->setParameter('objectId', $objectId)
					->select('Max(le.version)');

    	return $qb  ->getQuery()
    				->getSingleResult(); 
	}

    /**
     * Find last $limit version for the given $entity
     *
     * @param DataAbstractEntity $entity
     * @param integer $limit|5
     *
     * @return QueryBuilder
     */
    public function findAllVersion(DataAbstractEntity $entity, $limit = 5){

        $wrapped = new EntityWrapper($entity, $this->_em);
        $objectClass = $wrapped->getMetadata()->name;
        $objectId = $wrapped->getIdentifier();

        $qb = $this ->createQueryBuilder('le')
                    ->where('le.objectClass = :objectClass')
                    ->setParameter('objectClass', $objectClass)
                    ->andWhere('le.objectId = :objectId')
                    ->setParameter('objectId', $objectId)
                    ->setMaxResults($limit)
                    ->orderBy('le.version', 'DESC');

        return $qb; 
    }
}
