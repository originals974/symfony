<?php

namespace SL\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * DataListRepository
 *
 */
class DataListRepository extends EntityRepository
{
	/**
     * Find all DataList with associated DataListValue
     *
     * @return Collection A collection of DataList and DataListValue
     */ 
	public function findFullAll()
	{
		$qb = $this	->createQueryBuilder('dl')
				   	->leftJoin('dl.dataListValues','dlv')
				   	->addOrderBy('dl.displayOrder', 'ASC')
				   	->addOrderBy('dlv.displayOrder', 'ASC')
	               	->addSelect('dl,dlv');

	    return $qb 	->getQuery()
	              	->getResult();

	}

	/**
     * Find DataList with associated DataListValue
     *
     * @param int $id Id of DataList to select
     *
     * @return DataList DataList and DataListValue
     */ 
	public function findFullById($id)
	{
		$qb = $this	->createQueryBuilder('dl')
				   	->leftJoin('dl.dataListValues','dlv')
				   	->where('dl.id = :id')
           			->setParameter('id', $id)
				   	->addOrderBy('dl.displayOrder', 'ASC')
				   	->addOrderBy('dlv.displayOrder', 'ASC')
	               	->addSelect('dl,dlv');

	    return $qb 	->getQuery()
	              	->getSingleResult();

	}

	/**
   	* Select the max DataList display order
   	*
   	* @return Integer Max DataList display order 
   	*/
	public function findMaxDisplayOrder()
  	{
	    $qb = $this ->createQueryBuilder('dl')
	               	->select('MAX(dl.displayOrder)');

	    return $qb 	->getQuery()
	              	->getSingleScalarResult();
	}

   /**
   * Select enabled DataList
   *
   * @return Collection A collection of enabled DataList
   */
	public function findEnabledDataList()
   {
        return  $this ->createQueryBuilder('dl')
            		  ->where('dl.isEnabled = true')
                      ->orderBy('dl.displayOrder', 'ASC');

	}
}
