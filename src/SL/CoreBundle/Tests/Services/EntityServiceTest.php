<?php

namespace SL\CoreBundle\Tests\Services;

use Liip\FunctionalTestBundle\Test\WebTestCase; 
use Doctrine\Common\Collections\ArrayCollection;

class EntityServiceTest extends WebTestCase
{
	private $entityService; 
  private $doctrineService; 
  private $em; 
  private $fullEntityClass; 
  private $targetEntityClassSingle; 
  private $targetEntityClassMultiple; 
	private $entity; 
  private $propertyText; 
  private $propertyTextArea;
  private $propertyEmail;
  private $propertyMoney;
  private $propertyNumber;
  private $propertyPercent;
  private $propertyUrl;
  private $propertyChoice;
  private $propertyChoiceMultiple;
  private $propertyEntity;
  private $propertyEntityMultiple;
  private $propertyGenemuJqueryDate;

  public function setUp()
  {
    $this->entityService = $this->getContainer()->get('sl_core.entity'); 
    $this->doctrineService = $this->getContainer()->get('sl_core.doctrine'); 
    $this->testService = $this->getContainer()->get('sl_core.test'); 
    $this->em = $this->getContainer()->get('doctrine.orm.entity_manager'); 

    //Load fixtures
    $classes = array(
        'SL\CoreBundle\DataFixtures\ORM\Base\LoadFieldTypeData',
        'SL\CoreBundle\DataFixtures\ORM\Test\LoadEntityServiceTestData',
    );
    $this->loadFixtures($classes);

    //Generate Doctrine entity file and database structure
    $this->targetEntityClassSingle = $this->em->getRepository('SLCoreBundle:EntityClass\EntityClass')
                                              ->findOneByDisplayName('target_entity_class_single');

    $this->targetEntityClassMultiple = $this->em->getRepository('SLCoreBundle:EntityClass\EntityClass')
                                                ->findOneByDisplayName('target_entity_class_multiple');

    $this->doctrineService->generateEntityFile($this->targetEntityClassSingle);
    $this->doctrineService->generateEntityFile($this->targetEntityClassMultiple);

    $this->fullEntityClass = $this->em->getRepository('SLCoreBundle:EntityClass\EntityClass')
                                      ->findOneByDisplayName('entity_class');

    $this->doctrineService->generateEntityFile($this->fullEntityClass);

    //Populate targets entities
    $class = $this->doctrineService->getEntityNamespace($this->targetEntityClassSingle->getTechnicalName());
    $targetEntitySingle = new $class($this->targetEntityClassSingle->getId()); 
    $targetEntitySingle->setDisplayName('target_entity_single'); 
    $this->em->persist($targetEntitySingle); 

    $class = $this->doctrineService->getEntityNamespace($this->targetEntityClassMultiple->getTechnicalName());
    $targetEntities = new ArrayCollection();  
    for($i=1; $i<=3; $i++){
      ${'targetEntityMultiple'.$i} = new $class($this->targetEntityClassMultiple->getId()); 
      ${'targetEntityMultiple'.$i}->setDisplayName('target_entity_multiple_'.$i); 
      $this->em->persist(${'targetEntityMultiple'.$i}); 
      $targetEntities->add(${'targetEntityMultiple'.$i}); 
    }

    //Populate entity
    $class = $this->doctrineService->getEntityNamespace($this->fullEntityClass->getTechnicalName());
    $this->entity = new $class($this->fullEntityClass->getId()); 

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
      'property_entity' => $targetEntitySingle,
      'property_entity_multiple' => $targetEntities,
      'property_genemu_jquerydate' => new \DateTime('2000-01-01'),
    ); 

    $this->testService->populateEntity($this->fullEntityClass, $this->entity, $data); 
    $this->em->persist($this->entity); 

    $this->em->flush(); 

    $this->propertyText = $this->testService->getPropertyByDisplayName($this->fullEntityClass, 'property_text'); 
    $this->propertyTextArea = $this->testService->getPropertyByDisplayName($this->fullEntityClass, 'property_textarea');
    $this->propertyEmail = $this->testService->getPropertyByDisplayName($this->fullEntityClass, 'property_email');
    $this->propertyMoney = $this->testService->getPropertyByDisplayName($this->fullEntityClass, 'property_money');
    $this->propertyNumber = $this->testService->getPropertyByDisplayName($this->fullEntityClass, 'property_number');
    $this->propertyPercent = $this->testService->getPropertyByDisplayName($this->fullEntityClass, 'property_percent');
    $this->propertyUrl = $this->testService->getPropertyByDisplayName($this->fullEntityClass, 'property_url');
    $this->propertyChoice = $this->testService->getPropertyByDisplayName($this->fullEntityClass, 'property_choice');
    $this->propertyChoiceMultiple = $this->testService->getPropertyByDisplayName($this->fullEntityClass, 'property_choice_multiple');
    $this->propertyEntity = $this->testService->getPropertyByDisplayName($this->fullEntityClass, 'property_entity');
    $this->propertyEntityMultiple = $this->testService->getPropertyByDisplayName($this->fullEntityClass, 'property_entity_multiple');
    $this->propertyGenemuJqueryDate = $this->testService->getPropertyByDisplayName($this->fullEntityClass, 'property_genemu_jquerydate');
  }

  protected function tearDown()
  {
    $this->doctrineService->removeEntityFile($this->fullEntityClass);
    $this->doctrineService->removeEntityFile($this->targetEntityClassSingle);
    $this->doctrineService->removeEntityFile($this->targetEntityClassMultiple);
    $this->doctrineService->doctrineSchemaUpdateForce(); 

    unset(
      $this->entityService, 
      $this->doctrineService, 
      $this->em, 
      $this->fullEntityClass, 
      $this->targetEntityClassSingle,
      $this->targetEntityClassMultiple,
      $this->entity,
      $this->propertyText, 
      $this->propertyTextArea,
      $this->propertyEmail,
      $this->propertyMoney,
      $this->propertyNumber,
      $this->propertyPercent,
      $this->propertyUrl,
      $this->propertyChoice,
      $this->propertyChoiceMultiple,
      $this->propertyEntity,
      $this->propertyEntityMultiple,
      $this->propertyGenemuJqueryDate
      );
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
    $this->testService->populateEntity($this->fullEntityClass, $this->entity, array('property_text' => null)); 
    $this->em->flush();
    $propertyHasNotNullValues = $this->entityService->propertyHasNotNullValues($this->propertyText);

    $this->assertTrue(!$propertyHasNotNullValues);

    /**
    * #2
    * One property value is not null
    */
    $this->testService->populateEntity($this->fullEntityClass, $this->entity, array('property_text' => 'not null value')); 
    $this->em->flush();
    $propertyHasNotNullValues = $this->entityService->propertyHasNotNullValues($this->propertyText);

    $this->assertTrue($propertyHasNotNullValues);
  }

  public function testCalculateDisplayName()
  {
    $this->fullEntityClass->setCalculatedName(
      '%'.$this->propertyText->getTechnicalName().
      '% %'.$this->propertyTextArea->getTechnicalName().
      '% %'.$this->propertyEmail->getTechnicalName().
      '% %'.$this->propertyMoney->getTechnicalName().
      '% %'.$this->propertyNumber->getTechnicalName().
      '% %'.$this->propertyPercent->getTechnicalName().
      '% %'.$this->propertyUrl->getTechnicalName().
      '% %'.$this->propertyChoice->getTechnicalName().
      '% %'.$this->propertyChoiceMultiple->getTechnicalName().
      '% %'.$this->propertyEntity->getTechnicalName().
      '% %'.$this->propertyEntityMultiple->getTechnicalName().
      '% - (%'.$this->propertyGenemuJqueryDate->getTechnicalName().'%)'
      ); 

    $this->em->flush(); 

    $displayName = $this->entityService->calculateDisplayName($this->entity);

    $this->assertEquals('Test1 Test3 test@email.com 45.56 45.67 45.89 www.test.com choice1 choice multiple 1, choice multiple 2, choice multiple 3 target_entity_single target_entity_multiple_1, target_entity_multiple_2, target_entity_multiple_3 - (01/01/2000 00:00:00)', $displayName); 
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
    $this->fullEntityClass->setCalculatedName(
      '%'.$this->propertyText->getTechnicalName().
      '% %'.$this->propertyTextArea->getTechnicalName()
    );

    $this->entityService->refreshDisplayName($this->fullEntityClass);

    $entity = $this->em->getRepository('SLCoreBundle:Generated\\'.$this->fullEntityClass->getTechnicalName())->findOneById($this->entity->getId()); 

    $this->assertEquals('Test1 Test3', $entity->getDisplayName());
  }

  public function testEntitiesExist()
  {
    /**
      * #1
      * Entities exist for entity class
      */
    $entitiesExist = $this->entityService->entitiesExist($this->fullEntityClass);

    $this->assertTrue($entitiesExist);

    /**
      * #2
      * Entities don't exist for entity class
      */
    $this->em->remove($this->entity);
    $this->em->flush();  

    $entitiesExist = $this->entityService->entitiesExist($this->fullEntityClass);

    $this->assertTrue(!$entitiesExist);
  }
}