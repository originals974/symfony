<?php

namespace SL\CoreBundle\Tests\Services;

use Liip\FunctionalTestBundle\Test\WebTestCase; 
use Doctrine\Common\Collections\ArrayCollection;

class EntityServiceTest extends WebTestCase
{
	private $entityService; 
  private $doctrineService; 
  private $em; 
  private $databaseEm; 
  private $entityClass; 
  private $targetEntityClass1; 
  private $targetEntityClass2; 
	private $entity; 

  public function setUp()
  {
    $this->entityService = $this->getContainer()->get('sl_core.entity'); 
    $this->doctrineService = $this->getContainer()->get('sl_core.doctrine'); 
    $this->testService = $this->getContainer()->get('sl_core.test'); 
    $this->em = $this->getContainer()->get('doctrine.orm.entity_manager'); 
    $this->databaseEm = $this->getContainer()->get('doctrine')->getManager('database'); 

    //Load fixtures
    $classes = array(
        'SL\CoreBundle\DataFixtures\ORM\LoadFieldTypeData',
        'SL\CoreBundle\DataFixtures\ORM\Test\LoadEntityServiceTestData',
    );
    $this->loadFixtures($classes);

    //Generate Doctrine entity file and database structure
    for($i=1; $i<=2; $i++) {
      $this->{'targetEntityClass'.$i} = $this->em->getRepository('SLCoreBundle:EntityClass\EntityClass')
                              ->findOneByDisplayName('target_entity_class_'.$i);

      $this->doctrineService->generateEntityFileAndObjectSchema($this->{'targetEntityClass'.$i});
    }

    $this->entityClass = $this->em->getRepository('SLCoreBundle:EntityClass\EntityClass')
                              ->findOneByDisplayName('entity_class');

    $this->doctrineService->generateEntityFileAndObjectSchema($this->entityClass);

    //Populate targets entities
    $class = $this->doctrineService->getDataEntityNamespace($this->targetEntityClass1->getTechnicalName());
    $targetEntity1 = new $class($this->targetEntityClass1->getId()); 
    $targetEntity1->setDisplayName('target_entity_1'); 
    $this->databaseEm->persist($targetEntity1); 

    $class = $this->doctrineService->getDataEntityNamespace($this->targetEntityClass2->getTechnicalName());
    $targetEntities = new ArrayCollection();  
    for($i=2; $i<=4; $i++){
      ${'targetEntity'.$i} = new $class($this->targetEntityClass2->getId()); 
      ${'targetEntity'.$i}->setDisplayName('target_entity_'.$i); 
      $this->databaseEm->persist(${'targetEntity'.$i}); 
      $targetEntities->add(${'targetEntity'.$i}); 
    }

    //Populate entity
    $class = $this->doctrineService->getDataEntityNamespace($this->entityClass->getTechnicalName());
    $this->entity = new $class($this->entityClass->getId()); 

    $data = array(
      'property_text' => 'Test1',  
      'property_textarea' => 'Test3',
      'property_email' => 'test@email.com',
      'property_money' => '45.56',
      'property_number' => '45.67',
      'property_percent' => '45.89',
      'property_url' => 'www.test.com',
      'property_choice' => 'choice1',
      'property_choice_multiple' => array('choice multiple 1', 'choice multiple 2', 'choice multiple 3'),
      'property_entity' => $targetEntity1,
      'property_entity_multiple' => $targetEntities,
      'property_genemu_jquerydate' => new \DateTime('2000-01-01'),
    ); 

    $this->testService->populateEntity($this->entityClass, $this->entity, $data); 
    $this->databaseEm->persist($this->entity); 

    $this->databaseEm->flush(); 
  }

  protected function tearDown()
  {
    $this->doctrineService->removeEntityFile($this->entityClass);
    $this->doctrineService->removeEntityFile($this->targetEntityClass1);
    $this->doctrineService->removeEntityFile($this->targetEntityClass2);
    $this->doctrineService->doctrineSchemaUpdateForce(); 
    unset($this->entityService, $this->doctrineService, $this->em, $this->databaseEm, $this->entityClass, $this->targetEntityClass1, $this->entity);
  }

  public function testCreateCreateForm()
  {
    $form = $this->entityService->createCreateForm($this->entity);

    $this->assertInstanceOf('Symfony\Component\Form\Form', $form);
  }

  public function testCreateEditForm()
  {
    $form = $this->entityService->createEditForm($this->entity);

    $this->assertInstanceOf('Symfony\Component\Form\Form', $form);
  }

  public function testCreateDeleteForm()
  {
    $form = $this->entityService->createDeleteForm($this->entity);

    $this->assertInstanceOf('Symfony\Component\Form\Form', $form);
  }

  public function testCreateSearchForm()
  {
    $form = $this->entityService->createSearchForm();

    $this->assertInstanceOf('Symfony\Component\Form\Form', $form);
  }

  public function testCreateEditVersionForm()
  {
    $form = $this->entityService->createEditVersionForm($this->entity);

    $this->assertInstanceOf('Symfony\Component\Form\Form', $form);
  }

  public function testPropertyHasNotNullValues()
  {
    /**
    * #1
    * All properties values are null
    */
    $this->testService->populateEntity($this->entityClass, $this->entity, array('property_text' => null)); 
    $this->databaseEm->flush(); 
    $propertyHasNotNullValues = $this->entityService->propertyHasNotNullValues($this->testService->getPropertyByDisplayName($this->entityClass, 'property_text'));

    $this->assertTrue(!$propertyHasNotNullValues);

    /**
    * #2
    * One property value is not null
    */
    $this->testService->populateEntity($this->entityClass, $this->entity, array('property_text' => 'not null value')); 
    $this->databaseEm->flush();
    $propertyHasNotNullValues = $this->entityService->propertyHasNotNullValues($this->testService->getPropertyByDisplayName($this->entityClass, 'property_text'));

    $this->assertTrue($propertyHasNotNullValues);
  }

  public function testCalculateDisplayName()
  {
    $propertyText = $this->testService->getPropertyByDisplayName($this->entityClass, 'property_text'); 
    $propertyTextArea = $this->testService->getPropertyByDisplayName($this->entityClass, 'property_textarea');
    $propertyEmail = $this->testService->getPropertyByDisplayName($this->entityClass, 'property_email');
    $propertyMoney = $this->testService->getPropertyByDisplayName($this->entityClass, 'property_money');
    $propertyNumber = $this->testService->getPropertyByDisplayName($this->entityClass, 'property_number');
    $propertyPercent = $this->testService->getPropertyByDisplayName($this->entityClass, 'property_percent');
    $propertyUrl = $this->testService->getPropertyByDisplayName($this->entityClass, 'property_url');
    $propertyChoice = $this->testService->getPropertyByDisplayName($this->entityClass, 'property_choice');
    $propertyChoiceMultiple = $this->testService->getPropertyByDisplayName($this->entityClass, 'property_choice_multiple');
    $propertyEntity = $this->testService->getPropertyByDisplayName($this->entityClass, 'property_entity');
    $propertyEntityMultiple = $this->testService->getPropertyByDisplayName($this->entityClass, 'property_entity_multiple');
    $propertyGenemuJqueryDate = $this->testService->getPropertyByDisplayName($this->entityClass, 'property_genemu_jquerydate');

    $this->entityClass->setCalculatedName(
      '%'.$propertyText->getTechnicalName().
      '% %'.$propertyTextArea->getTechnicalName().
      '% %'.$propertyEmail->getTechnicalName().
      '% %'.$propertyMoney->getTechnicalName().
      '% %'.$propertyNumber->getTechnicalName().
      '% %'.$propertyPercent->getTechnicalName().
      '% %'.$propertyUrl->getTechnicalName().
      '% %'.$propertyChoice->getTechnicalName().
      '% %'.$propertyChoiceMultiple->getTechnicalName().
      '% %'.$propertyEntity->getTechnicalName().
      '% %'.$propertyEntityMultiple->getTechnicalName().
      '% %'.$propertyGenemuJqueryDate->getTechnicalName()
      ); 

    $this->em->flush(); 

    $displayName = $this->entityService->calculateDisplayName($this->entity);

    $this->assertEquals('Test1 Test3 test@email.com 45.56 45.67 45.89 www.test.com choice1 choice multiple 1, choice multiple 2, choice multiple 3 target_entity_1 target_entity_2, target_entity_3, target_entity_4 01/01/2000 00:00:00', $displayName); 
  }

  public function testRefreshDisplayName()
  {
    /**
    * #1
    * Before display name update
    */
    $this->assertEquals("entity", $this->entity->getDisplayName());

    /**
    * #2
    * After diplayname update
    */
    $propertyText = $this->testService->getPropertyByDisplayName($this->entityClass, 'property_text'); 
    $propertyTextArea = $this->testService->getPropertyByDisplayName($this->entityClass, 'property_textarea');
    $this->entityClass->setCalculatedName(
      '%'.$propertyText->getTechnicalName().
      '% %'.$propertyTextArea->getTechnicalName()
    );

    $this->entityService->refreshDisplayName($this->entityClass);

    $entity = $this->databaseEm->getRepository('SLDataBundle:'.$this->entityClass->getTechnicalName())->findOneById($this->entity->getId()); 

    $this->assertEquals('Test1 Test3', $entity->getDisplayName());
  }

  public function testEntitiesExist()
  {
    /**
      * #1
      * Entities exist for entity class
      */
    $entitiesExist = $this->entityService->entitiesExist($this->entityClass);

    $this->assertTrue($entitiesExist);

    /**
      * #2
      * Entities don't exist for entity class
      */
    $this->databaseEm->remove($this->entity);
    $this->databaseEm->flush();  

    $entitiesExist = $this->entityService->entitiesExist($this->entityClass);

    $this->assertTrue(!$entitiesExist);
  }
}