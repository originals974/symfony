<?php

namespace SL\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * ChoiceListRepository
 *
 */
class ChoiceListRepository extends EntityRepository
{
   /**
   * Select all choice lists with their items
   *
   * @param QueryBuilder $qb
   *
   * @return QueryBuilder
   */
	private function sharedQB(QueryBuilder $qb){

		$qb ->createQueryBuilder('cl')
			->leftJoin('cl.choiceItems','ci')
	        ->addSelect('ci')
	        ->orderBy('cl.position, ci.position', 'ASC');

    	return $qb; 
	}

	/**
     * Find all choice lists with their items
     *
     * @return QueryBuilder
     */ 
	public function fullFindAllQb()
	{
		$qb = $this->createQueryBuilder('dl');
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
     * Find choice list with $choiceListId
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
