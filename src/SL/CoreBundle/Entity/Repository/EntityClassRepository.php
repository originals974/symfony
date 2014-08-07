<?php

namespace SL\CoreBundle\Entity\Repository;

//Symfony classes
use Doctrine\ORM\QueryBuilder;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

//Custom classes
use SL\CoreBundle\Entity\EntityClass;

/**
 * EntityClassRepository
 *
 */
class EntityClassRepository extends NestedTreeRepository
{
  /**
   * Select all entityClasses and properties 
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
   * Select all entityClasses and properties 
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
   * Select entityClass and properties by entityClass id
   *
   * @param int $entityClassId
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
   * Select other entityClasses
   *
   * @param EntityClass $currentEntityClass
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
