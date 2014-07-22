<?php

namespace SL\CoreBundle\Entity;

//Doctrine classes
use Doctrine\ORM\EntityRepository;

//Custom classes
use SL\CoreBundle\Entity\DataList;

/**
 * DataListValueRepository
 *
 */
class DataListValueRepository extends EntityRepository
{
	/**
	 * Select enabled datalistvalue for a datalist
	 *
	 * @param DataList $dataList
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

