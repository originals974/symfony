<?php

namespace SL\CoreBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use SL\CoreBundle\Entity\Choice\ChoiceList; 

/**
 * ChoiceItemRepository
 *
 */
class ChoiceItemRepository extends EntityRepository
{
	/**
	 * Select all items of $choiceList
	 *
	 * @param SL\CoreBundle\Entity\Choice\ChoiceList $choiceList
	 *
	 * @return array
	 */
	public function fullFindByChoiceList(ChoiceList $choiceList){
		
	    $qb = $this ->createQueryBuilder('ci')
	    			->join('ci.choiceList','cl')
	                ->where('cl.id = :id')
	                ->setParameter('id', $choiceList->getId())
	                ->orderBy('cl.position', 'ASC'); 

	    return $qb  ->getQuery()
	                ->getResult();
	}
}

