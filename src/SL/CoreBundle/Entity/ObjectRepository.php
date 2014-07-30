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
   * @param boolean $isDocument
   * @param integer $level
   */
  public function fullFindAll($isDocument = null, $level = null){
    
    $qb = $this->createQueryBuilder('o');
    $qb = $this->sharedQB($qb);
               
    if($isDocument !== null){
      $qb->andWhere('o.isDocument = :isDocument')
         ->setParameter('isDocument', $isDocument);
    }

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
   * Select other object that aren't document
   *
   * @param Object $currentObject
   */
	public function findOtherObject($currentObjectId)
  {
    $qb = $this->createQueryBuilder('o')
              ->where('o.isDocument = :isDocument')
              ->setParameter('isDocument', false)
              ->andWhere('o.id <> :id')
              ->setParameter('id', $currentObjectId)
              ->orderBy('o.position', 'ASC');

    return  $qb;

	}

  /**
   * Select potential parent object
   *
   * @param Object $object Child object
   */
  public function findParentObject($object)
  {
    $qb = $this->createQueryBuilder('o')
               ->where('o.isDocument = :isDocument')
               ->setParameter('isDocument',$object->isDocument())
               ->andWhere('o.id <> :objectId')
               ->setParameter('objectId',$object->getId());

    return  $qb;

  }
}
