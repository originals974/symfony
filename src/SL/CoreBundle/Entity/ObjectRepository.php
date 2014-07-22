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
   * Select all Object with associated Property, subObject and their Property 
   *
   * @return Collection A collection of Object
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
   * Select all Object with associated Property
   *
   * @return Collection A collection of Objects
   */
  public function findAllActiveObjects(){
    
    $qb = $this->createQueryBuilder('o');
    $qb = $this ->findFullAll($qb)
                ->where('o.isDocument = false')
                ->andWhere('o.isEnabled = true');
    
    return $qb->getQuery()
              ->getResult();
  }


  public function findRootObjects(){
    
    $qb = $this->createQueryBuilder('o');
    $qb = $this ->findFullAll($qb)
                ->where('o.isDocument = false')
                ->andWhere('o.lvl = 0');
    
    return $qb->getQuery()
              ->getResult();
  }



  /**
   * Select all Document with associated Propertiy
   *
   * @return Collection A collection of Document
   */
  public function findAllActiveDocuments(){
    
    $qb = $this->createQueryBuilder('o');
    $qb = $this->findFullAll($qb)
               ->where('o.isDocument = true')
               ->andWhere('o.isEnabled = true'); 

    return $qb->getQuery()
              ->getResult();
  }


  public function findRootDocuments(){
    
    $qb = $this->createQueryBuilder('o');
    $qb = $this ->findFullAll($qb)
                ->where('o.isDocument = true')
                ->andWhere('o.lvl = 0');
    
    return $qb->getQuery()
              ->getResult();
  }

  /**
   * Select Object with associated Property
   *
   * @param int $id Id of Object to select
   *
   * @return Object Object with its Property
   */
  public function findFullById($id){
    
    $qb = $this->createQueryBuilder('o');
    $qb = $this->findFullAll($qb)
                ->where('o.id = :id')
                ->setParameter('id', $id);

    return $qb->getQuery()
              ->getSingleResult();
  }

  /**
   * Select max Object display order
   *
   * @param boolean $isDocument True if Object is a document
   *
   * @return Integer Max Object display order
   */
	public function findMaxDisplayOrder($isDocument)
  {
	    $qb = $this  ->createQueryBuilder('o')
	                 ->select('MAX(o.displayOrder)')
                   ->where('o.isDocument = :isDocument')
                   ->setParameter('isDocument', $isDocument);

	    return $qb->getQuery()
	              ->getSingleScalarResult();
	}

  /**
   * Select other Object that aren't Document
   *
   * @param Object $currentObject Current Object
   *
   * @return Collection A collection with all other Object
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
   * Select potentiel parent Object
   *
   * @return Collection A collection with potentiel parent Object
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
