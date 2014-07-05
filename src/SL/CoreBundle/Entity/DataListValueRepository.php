<?php

namespace SL\CoreBundle\Entity;

//Doctrine classes
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder; 

//Custom classes
use SL\CoreBundle\Entity\DataList;

/**
 * DataListValueRepository
 *
 */
class DataListValueRepository extends EntityRepository
{
	/**
   	* Select max DataListValue display order for a DataList
   	*
   	* @param DataList $dataList Parent DataList 
   	*
   	* @return Integer Max DataListValue display order
   	*/
	public function findMaxDisplayOrder(DataList $dataList)
  	{
	    $qb = $this->createQueryBuilder('dlv')
	               ->select('MAX(dlv.displayOrder)')
	               ->where('dlv.dataList = :dataList')
	               ->setParameter('dataList', $dataList);

	    return $qb->getQuery()
	              ->getSingleScalarResult();
	}

	/**
	 * Select enabled DataListValue for a DataList
	 *
	 * @param DataList $dataList DataList
	 *
	 * @return Collection  A collection of enabled DataListValue of DataList
	*/
	public function findEnabledByDataList(DataList $dataList){
		
	    $qb = $this ->createQueryBuilder('dlv')
	    			->join('dlv.dataList','dl')
	                ->where('dl.id = :id')
	                ->setParameter('id', $dataList->getId())
	                ->andWhere('dlv.isEnabled = true')
	                ->orderBy('dlv.displayOrder', 'ASC'); 

	    return $qb->getQuery()
	              ->getResult();
	}
}

