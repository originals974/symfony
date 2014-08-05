<?php

namespace SL\CoreBundle\Entity;

//Doctrine classes
use Doctrine\ORM\EntityRepository;


/**
 * ChoiceItemRepository
 *
 */
class ChoiceItemRepository extends EntityRepository
{
	/**
	 * Select all items of $choiceList
	 *
	 * @param ChoiceList $choiceList
	 *
	 * @return array
	 */
	public function fullFindByChoiceList(ChoiceList $choiceList){
		
	    $qb = $this ->createQueryBuilder('ci')
	    			->join('ci.choiceList','cl')
	                ->where('cl.id = :id')
	                ->setParameter('id', $choiceList->getId())
	                ->orderBy('dlv.position', 'ASC'); 

	    return $qb->getQuery()
	              ->getResult();
	}
}

