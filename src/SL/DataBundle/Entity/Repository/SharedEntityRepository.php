<?php

namespace SL\DataBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use SL\CoreBundle\Entity\EntityClass\Property; 

/**
 * AbstractEntityRepository
 *
 */
class SharedEntityRepository extends EntityRepository
{
	 /**
     * Find number of not null values $property database field 
     *
     * @param Property $property
     *
     * @return array
     */
	public function findNotNullValuesByProperty(Property $property){
		$qb = $this ->createQueryBuilder('e')
					->select('COUNT(e.id)');  

		if($property->getFieldType()->getFormType() == "entity"){
			$qb ->join('e.'.$property->getTechnicalName(),'ej');
		}
		else {
			$qb ->where($qb->expr()->isNotNull('e.'.$property->getTechnicalName()));
		}
	    
	    return $qb  ->getQuery()
	                ->getSingleResult();
	}
}

