<?php

namespace SL\CoreBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * ChoiceListRepository
 *
 */
class ChoiceListRepository extends EntityRepository
{
   /**
   * Shared QueryBuilder
   *
   * @param Doctrine\ORM\QueryBuilder $qb
   *
   * @return Doctrine\ORM\QueryBuilder $qb
   */
	private function sharedQB(QueryBuilder $qb){

		$qb ->leftJoin('cl.choiceItems','ci')
	        ->addSelect('ci')
	        ->orderBy('cl.position, ci.position', 'ASC');

    	return $qb; 
	}

	/**
     * Find all choice lists with their items
     *
     * @return Doctrine\ORM\QueryBuilder $qb
     */ 
	public function fullFindAllQb()
	{
		$qb = $this->createQueryBuilder('cl');
		$qb = $this->sharedQB($qb);

	    return $qb;

	}

	/**
     * Find all choice lists with their items
     *
     * @return array
     */ 
	public function fullFindAll()
	{
	    return $this->fullFindAllQb()
	    			->getQuery()
	              	->getResult();

	}

	/**
     * Find choice list identified by $choiceListId
     * and its items
     *
     * @param integer $choiceListId
     *
     * @return array
     */ 
	public function fullFindById($choiceListId)
	{
		$qb = $this->createQueryBuilder('cl');
		$qb = $this->sharedQB($qb);

		$qb ->where('cl.id = :id')
			->setParameter('id', $choiceListId);

	    return $qb 	->getQuery()
	              	->getSingleResult();

	}
}
