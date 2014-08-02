<?php

namespace SL\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * DataListRepository
 *
 */
class DataListRepository extends EntityRepository
{
   /**
   * Select all data list and data list value 
   *
   * @param QueryBuilder $qb
   */
	private function sharedQB(QueryBuilder $qb){

		$qb = $this	->createQueryBuilder('dl')
				   	->leftJoin('dl.dataListValues','dlv')
	               	->addSelect('dlv')
	               	->orderBy('dl.position, dlv.position', 'ASC');

    	return $qb; 
	}

	/**
     * Find all datalist and datalistvalue
     */ 
	public function fullFindAllQb()
	{
		$qb = $this->createQueryBuilder('dl');
		$qb = $this->sharedQB($qb);

	    return $qb;

	}

	/**
     * Find all datalist and datalistvalue
     */ 
	public function fullFindAll()
	{
	    return $this->fullFindAllQb()
	    			->getQuery()
	              	->getResult();

	}

	/**
     * Find all datalist and datalistvalue by datalist id
     *
     * @param integer $dataListId
     */ 
	public function fullFindById($dataListId)
	{
		$qb = $this->createQueryBuilder('dl');
		$qb = $this->sharedQB($qb);

		$qb ->where('dl.id = :id')
			->setParameter('id', $dataListId);

	    return $qb 	->getQuery()
	              	->getSingleResult();

	}
}
