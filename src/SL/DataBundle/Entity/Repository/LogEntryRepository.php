<?php

namespace SL\DataBundle\Entity\Repository;

use Gedmo\Loggable\Entity\Repository\LogEntryRepository as BaseLogEntryRepository;
use Gedmo\Tool\Wrapper\EntityWrapper;
use SL\DataBundle\Entity\MappedSuperclass\AbstractEntity;

class LogEntryRepository extends BaseLogEntryRepository
{
    /**
     * Find current version for $entity
     *
     * @param AbstractEntity $entity
     *
     * @return array
     */
	public function findCurrentVersion(AbstractEntity $entity){

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
     * @param Mixed $entity
     * @param integer $limit
     *
     * @return QueryBuilder
     */
    public function findAllVersion(AbstractEntity $entity, $limit = 5){

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
