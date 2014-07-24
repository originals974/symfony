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
	public function findFullAll(QueryBuilder $qb){

		$qb ->leftjoin('o.properties','p')
        ->addOrderBy('o.lvl', 'ASC')
  		  ->addOrderBy('o.displayOrder', 'ASC')
  		  ->addOrderBy('p.displayOrder', 'ASC')
        ->addSelect('p');

    return $qb; 
	}

  /**
   * Select all active objects and properties 
   */
  public function findAllEnabledObjects(){
    
    $qb = $this->createQueryBuilder('o');
    $qb = $this ->findFullAll($qb)
                ->where('o.isDocument = false')
                ->andWhere('o.isEnabled = true');
    
    return $qb->getQuery()
              ->getResult();
  }

  /**
   * Select all root objects and properties 
   */
  public function findRootObjects(){
    
    $qb = $this->createQueryBuilder('o');
    $qb = $this ->findFullAll($qb)
                ->where('o.isDocument = false')
                ->andWhere('o.lvl = 0');
    
    return $qb->getQuery()
              ->getResult();
  }

  /**
   * Select all active documents and properties 
   */
  public function findAllEnabledDocuments(){
    
    $qb = $this->createQueryBuilder('o');
    $qb = $this->findFullAll($qb)
               ->where('o.isDocument = true')
               ->andWhere('o.isEnabled = true'); 

    return $qb->getQuery()
              ->getResult();
  }

  /**
   * Select all root documents and properties 
   */
  public function findRootDocuments(){
    
    $qb = $this->createQueryBuilder('o');
    $qb = $this ->findFullAll($qb)
                ->where('o.isDocument = true')
                ->andWhere('o.lvl = 0');
    
    return $qb->getQuery()
              ->getResult();
  }

  /**
   * Select object and properties by object id
   *
   * @param int $objectId
   */
  public function findFullById($objectId){
    
    $qb = $this->createQueryBuilder('o');
    $qb = $this->findFullAll($qb)
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
              ->where('o.isEnabled = true')
              ->andWhere('o.isDocument = :isDocument')
              ->setParameter('isDocument', false)
              ->andWhere('o.id <> :id')
              ->setParameter('id', $currentObjectId)
              ->orderBy('o.displayOrder', 'ASC');

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
              //->where('o.isEnabled = true')
              ->where('o.isDocument = :isDocument')
              ->setParameter('isDocument',$object->isDocument())
              ->orderBy('o.displayOrder', 'ASC');

    if($object->getId()){
      $qb->andWhere('o.id <> :objectId')
         ->setParameter('objectId',$object->getId());
    }

    return  $qb;

  }
}
