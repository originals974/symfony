<?php

namespace SL\CoreBundle\Entity;

//Symfony classes
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

//Custom classes
use SL\CoreBundle\Entity\Object;

/**
 * ObjectRepository
 *
 */
class ObjectRepository extends NestedTreeRepository
{
  /**
   * Select all objects and properties 
   *
   * @param QueryBuilder $qb
   */
	private function sharedQB(QueryBuilder $qb){

		$qb ->leftjoin('o.properties','p')
        ->orderBy('o.position, p.position', 'ASC')
        ->addSelect('p');

    return $qb; 
	}

  /**
   * Select all objects and properties 
   *
   * @param integer $level
   */
  public function fullFindAll($level = null){
    
    $qb = $this->createQueryBuilder('o');
    $qb = $this->sharedQB($qb);

    if($level !== null){
      $qb->andWhere('o.lvl = :level')
         ->setParameter('level', $level);
    }

    return $qb->getQuery()
              ->getResult();
  }

  /**
   * Select object and properties by object id
   *
   * @param int $objectId
   */
  public function fullFindById($objectId){
    
    $qb = $this->createQueryBuilder('o');
    $qb = $this->sharedQB($qb)
                ->where('o.id = :id')
                ->setParameter('id', $objectId);

    return $qb->getQuery()
              ->getSingleResult();    
  }

  /**
   * Select other objects
   *
   * @param Object $currentObject
   */
	public function findOtherObject($currentObjectId)
  {
    $qb = $this->createQueryBuilder('o')
              ->where('o.id <> :id')
              ->setParameter('id', $currentObjectId)
              ->orderBy('o.position', 'ASC');

    return  $qb;

	}
}
