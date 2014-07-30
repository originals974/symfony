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
	 * Select data list value for datalist
	 *
	 * @param DataList $dataList
	 */
	public function fullFindByDataList(DataList $dataList){
		
	    $qb = $this ->createQueryBuilder('dlv')
	    			->join('dlv.dataList','dl')
	                ->where('dl.id = :id')
	                ->setParameter('id', $dataList->getId())
	                ->orderBy('dlv.position', 'ASC'); 

	    return $qb->getQuery()
	              ->getResult();
	}
}

