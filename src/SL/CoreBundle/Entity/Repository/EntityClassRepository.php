<?php

namespace SL\CoreBundle\Entity\Repository;

use Doctrine\ORM\QueryBuilder;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

/**
 * EntityClassRepository
 *
 */
class EntityClassRepository extends NestedTreeRepository
{
  /**
   * Shared QueryBuilder
   *
   * @param Doctrine\ORM\QueryBuilder $qb
   *
   * @return Doctrine\ORM\QueryBuilder $qb
   */
	private function sharedQB(QueryBuilder $qb){

		$qb ->leftjoin('o.properties','p')
        ->orderBy('o.position, p.position', 'ASC')
        ->addSelect('p');

    return $qb; 
	}

  /**
   * Select all entity classes with their properties 
   *
   * @param integer $level|null
   *
   * @return array
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
   * Select entity class identified by $entityClassId
   * and its properties
   *
   * @param integer $entityClassId
   *
   * @return array
   */
  public function fullFindById($entityClassId){
    
    $qb = $this->createQueryBuilder('o');
    $qb = $this->sharedQB($qb)
                ->where('o.id = :id')
                ->setParameter('id', $entityClassId);

    return $qb->getQuery()
              ->getSingleResult();    
  }

  /**
   * Select all entity classes 
   * except entity class identified by $currentEntityClassId
   *
   * @param integer $currentEntityClassId
   *
   * @return Doctrine\ORM\QueryBuilder $qb
   */
	public function findOtherEntityClass($currentEntityClassId)
  {
    $qb = $this->createQueryBuilder('o')
              ->where('o.id <> :id')
              ->setParameter('id', $currentEntityClassId)
              ->orderBy('o.position', 'ASC');

    return  $qb;

	}
}
