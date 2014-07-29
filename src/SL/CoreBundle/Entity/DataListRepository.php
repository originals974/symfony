<?php

namespace SL\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * DataListRepository
 *
 */
class DataListRepository extends EntityRepository
{
	/**
     * Find all datalist and datalistvalue
     */ 
	public function findFullAll()
	{
		$qb = $this	->createQueryBuilder('dl')
				   	->leftJoin('dl.dataListValues','dlv')
	               	->addSelect('dl,dlv');

	    return $qb 	->getQuery()
	              	->getResult();

	}

	/**
     * Find all datalist and datalistvalue by datalist id
     *
     * @param int $dataListId
     */ 
	public function findFullById($dataListId)
	{
		$qb = $this	->createQueryBuilder('dl')
				   	->leftJoin('dl.dataListValues','dlv')
				   	->where('dl.id = :id')
           			->setParameter('id', $dataListId)
	               	->addSelect('dl,dlv');

	    return $qb 	->getQuery()
	              	->getSingleResult();

	}

   /**
   * Select enabled datalist
   */
	public function findEnabledDataList()
   {
        return  $this ->createQueryBuilder('dl')
            		  ->where('dl.isEnabled = true');
	}
}
