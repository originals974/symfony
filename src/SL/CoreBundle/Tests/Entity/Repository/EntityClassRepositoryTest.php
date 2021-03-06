<?php

namespace SL\CoreBundle\Tests\Services;

use Liip\FunctionalTestBundle\Test\WebTestCase;

class EntityClassRepositoryTest extends WebTestCase
{
    private $em; 
    private $entityClassRepository;

    public function setUp()
    {
        //Load fixtures
        $classes = array(
            'SL\CoreBundle\DataFixtures\ORM\Test\LoadEntityClassRepositoryTestData',
        );
        $this->loadFixtures($classes);

        $this->em = $this->getContainer()->get('doctrine')->getManager(); 
        $this->entityClassRepository = $this->em->getRepository('SLCoreBundle:EntityClass\EntityClass'); 
    }

    protected function tearDown()
    {
       unset($this->em, $this->entityClassRepository); 
    }

    public function testFullFindAll()
    {
        /**
        * #1
        * Without parameter
        */
        $entityClasses = $this->entityClassRepository->fullFindAll(); 
        $this->assertCount(10, $entityClasses); 

        /**
        * #2
        * Find root entity class
        */
        $entityClasses = $this->entityClassRepository->fullFindAll(0); 
        $this->assertCount(10, $entityClasses); 

        /**
        * #3
        * Find entity classes with parent
        */
        $entityClasses = $this->entityClassRepository->fullFindAll(1); 
        $this->assertCount(0, $entityClasses); 

        /**
        * #4
        * Find entity classes with parent
        */
        $entityClass1 = $this->entityClassRepository->findOneByDisplayName('entity_class_1'); 
        $entityClass2 = $this->entityClassRepository->findOneByDisplayName('entity_class_2'); 
        $entityClass2->setParent($entityClass1); 
        $this->em->flush(); 

        $entityClasses = $this->entityClassRepository->fullFindAll(1); 
        $this->assertCount(1, $entityClasses); 
    }

    public function testFullFindById()
    {
        $entityClass1 = $this->entityClassRepository->findOneByDisplayName('entity_class_1'); 
        $entityClass = $this->entityClassRepository->fullFindById($entityClass1->getId()); 

        $this->assertInstanceOf('SL\CoreBundle\Entity\EntityClass\EntityClass', $entityClass);
    }

    public function testFindOtherEntityClass()
    {
        $entityClass1 = $this->entityClassRepository->findOneByDisplayName('entity_class_1'); 
        $qb = $this->entityClassRepository->findOtherEntityClass($entityClass1->getId()); 

        $this->assertInstanceOf('Doctrine\ORM\QueryBuilder', $qb);
        $this->assertCount(9, $qb->getQuery()->getResult());
    }
}