<?php

namespace SL\CoreBundle\Entity;

//Symfony classes
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

//Custom classes
use SL\CoreBundle\Entity\Object;

/**
 * ObjectRepository
 *
 */
class ObjectRepository extends EntityRepository
{
  /**
   * Select all Object with associated Property, subObject and their Property 
   *
   * @return Collection A collection of Object
   */
	public function findFullAll(QueryBuilder $qb){

    $subQuery = $this->getEntityManager()->createQuery("
                    SELECT parentObject
                    FROM SLCoreBundle:Object parentObject
                    JOIN parentObject.children childObject
                    WHERE childObject.id = o.id");
                
		$qb ->join('o.properties','p')
        ->leftJoin('o.children','co')
        ->leftJoin('co.properties', 'cop')
        ->where($qb->expr()->not($qb->expr()->exists($subQuery->getDql())))
  		  ->addOrderBy('o.displayOrder', 'ASC')
  		  ->addOrderBy('p.displayOrder', 'ASC')
        ->addOrderBy('co.displayOrder', 'ASC')
        ->addOrderBy('cop.displayOrder', 'ASC')
        ->addSelect('o,p,co,cop');

    return $qb; 
	}

  /**
   * Select Object with associated Property
   *
   * @param int $id Id of Object to select
   *
   * @return Object Object with its Property
   */
  public function findFullById($id){
    
    $qb = $this ->createQueryBuilder('o')
                ->leftJoin('o.properties','p')
                ->where('o.id = :id')
                ->setParameter('id', $id)
                ->addOrderBy('o.displayOrder', 'ASC')
                ->addOrderBy('p.displayOrder', 'ASC')
                ->addSelect('o,p');

    return $qb->getQuery()
              ->getSingleResult();
  }


  /**
   * Select all Object with associated Property
   *
   * @return Collection A collection of Objects
   */
  public function findFullAllObject(){
    
    $qb = $this->createQueryBuilder('o');
    $qb = $this ->findFullAll($qb)
                ->andWhere('o.isDocument = false');
    
    return $qb->getQuery()
              ->getResult();
  }

  /**
   * Select all Document with associated Propertiy
   *
   * @return Collection A collection of Document
   */
  public function findFullAllDocument(){
    
    $qb = $this->createQueryBuilder('o');
    $qb = $this->findFullAll($qb)
               ->andWhere('o.isDocument = true'); 

    return $qb->getQuery()
              ->getResult();
  }

  /**
   * Select max Object display order
   *
   * @param Object $parentObject Parent object of the object
   *
   * @return Integer Max Object display order
   */
	public function findMaxDisplayOrder(Object $parentObject=null)
  {
	    $qb = $this  ->createQueryBuilder('o')
	                 ->select('MAX(o.displayOrder)');

      if($parentObject != null){
        $qb ->join('o.parent', 'p')
            ->where('p.id = :id') 
            ->setParameter('id', $parentObject->getId());
      }
      else{
        $qb ->where('o.isParent = true'); 
      }

	    return $qb->getQuery()
	              ->getSingleScalarResult();
	}

  /**
   * Select target Object
   *
   * @param Object $excludeObject Object to exclude of returned values. Its parent and children are excluded too.
   *
   * @return Collection A collection of target Object
   */
	public function findTargertObject(Object $excludeObject)
  {
    $notIn = array($excludeObject->getId()); 

    $qb = $this->createQueryBuilder('o');

    //Select parent Object
    $notInParent = $this->createQueryBuilder('o')
                    ->select('po.id')
                    ->leftJoin('o.parent', 'po')
                    ->where('o.id = :id')
                    ->setParameter('id', $excludeObject->getId())
                    ->getQuery()
                    ->getSingleScalarResult();

    if($notInParent != null){
      array_push($notIn, $notInParent);
    }

    //Select children Object
    $notInChildren = $this->createQueryBuilder('o')
                      ->select('co.id')
                      ->leftJoin('o.children', 'co')
                      ->where('o.id = :id')
                      ->setParameter('id', $excludeObject->getId())
                      ->getQuery()
                      ->getResult();

    foreach($notInChildren as $notInChild){
      if($notInChild != null){
        array_push($notIn, $notInChild['id']);
      }
    }

    return  $this ->createQueryBuilder('o')
              ->where('o.isEnabled = true')
              ->andWhere('o.isDocument = :isDocument')
              ->setParameter('isDocument', false)
              ->andWhere($qb->expr()->not($qb->expr()->in('o.id', $notIn)))
              ->orderBy('o.displayOrder', 'ASC');

	}
}
