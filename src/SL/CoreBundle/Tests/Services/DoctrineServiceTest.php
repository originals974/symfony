<?php

namespace SL\CoreBundle\Tests\Services;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Doctrine\ORM\Tools\SchemaTool;

class DoctrineServiceTest extends WebTestCase
{
	private $doctrineService; 
    private $testService;
    private $em; 
    private $databaseEm;
    private $fullEntityClass; 
    private $simpleEntityClass; 
    private $propertyEntity; 
    private $propertyEntityMultiple;  
    private $entity; 
    private $data; 

    public function setUp()
    {
        $this->doctrineService = $this->getContainer()->get('sl_core.doctrine'); 
        $this->testService = $this->getContainer()->get('sl_core.test'); 
        $this->em = $this->getContainer()->get('doctrine')->getManager();
        $this->databaseEm = $this->getContainer()->get('doctrine')->getManager('database');
            
        $classes = array(
            'SL\CoreBundle\DataFixtures\ORM\LoadFieldTypeData',
            'SL\CoreBundle\DataFixtures\ORM\Test\LoadDoctrineServiceTestData',
        );
        $this->loadFixtures($classes);

        $this->fullEntityClass = $this->em->getRepository('SLCoreBundle:EntityClass\EntityClass')
                                          ->findOneByDisplayName('entity_class'); 

        $this->simpleEntityClass = $this->em->getRepository('SLCoreBundle:EntityClass\EntityClass')
                                        ->findOneByDisplayName('entity_class_1');  

        $this->propertyEntity = $this->testService->getPropertyByDisplayName($this->fullEntityClass, 'property_entity'); 
        $this->propertyEntityMultiple = $this->testService->getPropertyByDisplayName($this->fullEntityClass, 'property_entity_multiple');                                        
   
        $this->doctrineService->generateEntityFileAndObjectSchema($this->propertyEntity->getTargetEntityClass());
        $this->doctrineService->generateEntityFileAndObjectSchema($this->propertyEntityMultiple->getTargetEntityClass());
        $this->doctrineService->generateEntityFileAndObjectSchema($this->fullEntityClass);
    }

    protected function tearDown()
    {
        $this->doctrineService->removeEntityFile($this->fullEntityClass);
        $this->doctrineService->removeEntityFile($this->propertyEntity->getTargetEntityClass());
        $this->doctrineService->removeEntityFile($this->propertyEntityMultiple->getTargetEntityClass());
        $this->doctrineService->doctrineSchemaUpdateForce(); 

        unset(
            $this->doctrineService, 
            $this->testService,
            $this->em, 
            $this->databaseEm,
            $this->fullEntityClass,
            $this->simpleEntityClass,
            $this->propertyEntity,
            $this->propertyEntityMultiple,
            $this->entity,
            $this->data
            );
    }

    public function testGenerateEntityFileAndObjectSchema()
    {
        $this->assertFileExists('/home/samuel/Sites/symfony/src/SL/DataBundle/Entity/'.$this->propertyEntity->getTargetEntityClass()->getTechnicalName().'.php');
        $this->assertFileExists('/home/samuel/Sites/symfony/src/SL/DataBundle/Entity/'.$this->propertyEntityMultiple->getTargetEntityClass()->getTechnicalName().'.php');
        $this->assertFileExists('/home/samuel/Sites/symfony/src/SL/DataBundle/Entity/'.$this->fullEntityClass->getTechnicalName().'.php');
    }

    public function testRemoveEntityFile()
    {
        $this->doctrineService->generateEntityFileAndObjectSchema($this->propertyEntity->getTargetEntityClass());
        $this->assertFileExists('/home/samuel/Sites/symfony/src/SL/DataBundle/Entity/'.$this->propertyEntity->getTargetEntityClass()->getTechnicalName().'.php');

        $this->doctrineService->removeEntityFile($this->propertyEntity->getTargetEntityClass());
        $this->assertFileNotExists('/home/samuel/Sites/symfony/src/SL/DataBundle/Entity/'.$this->propertyEntity->getTargetEntityClass()->getTechnicalName().'.php');
    }

    public function testGetDataEntityNamespace()
    { 
       $entityNamespace = $this->doctrineService->getDataEntityNamespace('EntityClass1');

       $this->assertEquals($entityNamespace, 'SL\DataBundle\Entity\EntityClass1'); 
    }

    public function testEntityDelete()
    { 
        /**
        * #1
        * Soft delete
        */
        $this->assertNull($this->fullEntityClass->getDeletedAt()); 
        $this->doctrineService->entityDelete($this->fullEntityClass);

        $filters = $this->em->getFilters();
        $filters->disable('softdeleteable');

        $this->fullEntityClass = $this->em->getRepository('SLCoreBundle:EntityClass\EntityClass')
                                          ->findOneByDisplayName('entity_class'); 
            
        $filters->enable('softdeleteable');

        $this->assertEquals('entity_class', $this->fullEntityClass->getDisplayName()); 
        $this->assertNotNull($this->fullEntityClass->getDeletedAt());

        /**
        * #2
        * Hard delete
        */

        $this->assertNull($this->simpleEntityClass->getDeletedAt()); 
                                 
        $this->doctrineService->entityDelete($this->simpleEntityClass, true);

        $filters = $this->em->getFilters();
        $filters->disable('softdeleteable');

        $this->simpleEntityClass = $this->em->getRepository('SLCoreBundle:EntityClass\EntityClass')
                                 ->findOneByDisplayName('entity_class_1');
            
        $filters->enable('softdeleteable');

        $this->assertNull($this->simpleEntityClass);
    }

    public function testGetFormatedLogEntries()
    {
        //Generate version 1
        $class = $this->doctrineService->getDataEntityNamespace($this->fullEntityClass->getTechnicalName());
        $this->entity = new $class($this->fullEntityClass->getId()); 

        $dataV1 = array(
          'property_text' => 'Test1 (#1)',  
          'property_textarea' => 'Test3 (#1)',
          'property_email' => 'test#1@email.com',
          'property_money' => '145.56',
          'property_number' => '145.67',
          'property_percent' => '145.89',
          'property_url' => 'www.test#1.com',
          'property_choice' => 'choice1 (#1)',
          'property_choice_multiple' => array('choice multiple 1 (#1)', 'choice multiple 2 (#1)', 'choice multiple 3 (#1)'),
          'property_genemu_jquerydate' => new \DateTime('2001-01-01'),
        ); 

        $this->testService->populateEntity($this->fullEntityClass, $this->entity, $dataV1); 
        $this->databaseEm->persist($this->entity); 

        $this->databaseEm->flush();

        //Generate version 2
        $dataV2 = array(
          'property_text' => 'Test1 (#2)',  
          'property_textarea' => 'Test3 (#2)',
          'property_email' => 'test#2@email.com',
          'property_money' => '245.56',
          'property_number' => '245.67',
          'property_percent' => '245.89',
          'property_url' => 'www.test#2.com',
          'property_choice' => 'choice1 (#2)',
          'property_choice_multiple' => array('choice multiple 1 (#2)', 'choice multiple 2 (#2)', 'choice multiple 3 (#2)'),
          'property_genemu_jquerydate' => new \DateTime('2002-01-01'),
        ); 

        $this->testService->populateEntity($this->fullEntityClass, $this->entity, $dataV2); 
        $this->databaseEm->flush(); 

        //Generate version 3
        $dataV3 = array(
          'property_text' => 'Test1 (#3)',  
          'property_textarea' => 'Test3 (#3)',
          'property_email' => 'test#3@email.com',
          'property_money' => '145.56',
          'property_number' => '145.67',
          'property_percent' => '145.89',
          'property_url' => 'www.test#2.com',
          'property_choice' => 'choice1 (#2)',
          'property_choice_multiple' => array('choice multiple 1 (#2)', 'choice multiple 2 (#2)', 'choice multiple 3 (#2)'),
          'property_genemu_jquerydate' => new \DateTime('2003-01-01'),
        ); 

        $this->testService->populateEntity($this->fullEntityClass, $this->entity, $dataV3); 
        $this->databaseEm->flush(); 

        $formatedLogEntries = $this->doctrineService->getFormatedLogEntries($this->entity); 

        $this->assertCount(3, $formatedLogEntries);

        $this->assertEquals(3, $formatedLogEntries[0]['version']);
        $this->assertEquals(2, $formatedLogEntries[1]['version']);
        $this->assertEquals(1, $formatedLogEntries[2]['version']);

        $datas = array($dataV3, $dataV2, $dataV1);

        foreach($datas as $dataKey => $data){

            foreach($data as $key => $value){

                $property = $this->testService->getPropertyByDisplayName($this->fullEntityClass, $key); 
                $this->assertEquals($value, $formatedLogEntries[$dataKey]['data']->{'get'.$property->getTechnicalName()}());
            }
        }
    } 
}