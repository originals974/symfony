<?php

namespace SL\CoreBundle\Tests\Services\EntityClass;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use SL\CoreBundle\Services\EntityClass\EntityClassService; 

class EntityClassServiceTest extends WebTestCase
{
	private $entityClassService; 
  private $testService; 
	private $em; 
	private $translator;
  private $fullEntityClass; 
  private $targetEntityClass; 
  private $simpleEntityClass; 
  private $entityClassWithParent; 
  
  public function setUp()
  {
    $this->entityClassService = $this->getContainer()->get('sl_core.entity_class'); 
    $this->testService = $this->getContainer()->get('sl_core.test'); 
    $this->em = $this->getContainer()->get('doctrine.orm.entity_manager'); 
    $this->translator = $this->getContainer()->get('translator'); 

    $classes = array(
        'SL\CoreBundle\DataFixtures\ORM\Base\LoadFieldTypeData',
        'SL\CoreBundle\DataFixtures\ORM\Test\LoadEntityClassServiceTestData',
    );
    $this->loadFixtures($classes);

    $this->fullEntityClass = $this->em->getRepository('SLCoreBundle:EntityClass\EntityClass')
                                ->findOneByDisplayName('entity_class'); 

    $this->targetEntityClass = $this->em->getRepository('SLCoreBundle:EntityClass\EntityClass')
                                        ->findOneByDisplayName('target_entity_class_single'); 

    $this->simpleEntityClass = $this->em->getRepository('SLCoreBundle:EntityClass\EntityClass')
                                        ->findOneByDisplayName('entity_class_1');  

    $this->entityClassWithParent = $this->em->getRepository('SLCoreBundle:EntityClass\EntityClass')
                                            ->findOneByDisplayName('entity_class_10'); 
          
  }

  protected function tearDown()
	{
	  unset(
      $this->entityClassService, 
      $this->testService, 
      $this->em, 
      $this->translator, 
      $this->fullEntityClass,
      $this->targetEntityClass,
      $this->simpleEntityClass,
      $this->entityClassWithParent
      );
	}

  public function testCreateCreateForm()
  {
  	/**
    * #1
    * With parent == null
    */
    $form = $this->entityClassService->createCreateForm($this->simpleEntityClass);
   	$this->assertInstanceOf('Symfony\Component\Form\Form', $form);
   	
   	/**
    * #2
    * With parent != null
    */
    $form = $this->entityClassService->createCreateForm($this->entityClassWithParent);
   	$this->assertInstanceOf('Symfony\Component\Form\Form', $form);

  }

  public function testCreateEditForm()
  {
    $form = $this->entityClassService->createEditForm($this->simpleEntityClass);
   	$this->assertInstanceOf('Symfony\Component\Form\Form', $form);
  }

  public function testCreateDeleteForm()
  {
    $form = $this->entityClassService->createDeleteForm($this->simpleEntityClass);
   	$this->assertInstanceOf('Symfony\Component\Form\Form', $form);
  }

  public function testCreateEditCalculatedNameForm()
  {
    $form = $this->entityClassService->createEditCalculatedNameForm($this->simpleEntityClass);
   	$this->assertInstanceOf('Symfony\Component\Form\Form', $form);
  }

  public function testIntegrityControlBeforeDelete()
  {
  	/**
    * #1
    * Not linked to other entity class
    */
    $integrityError = $this->entityClassService->integrityControlBeforeDelete($this->simpleEntityClass);
   	$this->assertNull($integrityError);

   	/**
    * #2
    * Linked to other entity class
    */
    $integrityError = $this->entityClassService->integrityControlBeforeDelete($this->targetEntityClass);
   	
    $expectedIntegrityError = array(
        'title' => $this->translator->trans('delete.error.title'),
        'message' => $this->translator->trans('entity_class.delete.reference.error.message')
        );

   	$this->assertEquals($expectedIntegrityError, $integrityError);
  }

  public function testInitCalculatedName()
  {
    /**
    * #1
    * Object with no parent
    */
    $calculatedName = $this->entityClassService->initCalculatedName($this->fullEntityClass);
    $calculatedNamePattern ='%'.$this->fullEntityClass->getProperties()->first()->getTechnicalName().'%';

    $this->assertEquals($calculatedNamePattern , $calculatedName);

    /**
    * #2
    * Object with parent
    */
    $propertyText = $this->testService->getPropertyByDisplayName($this->fullEntityClass, 'property_text'); 
    $propertyEmail = $this->testService->getPropertyByDisplayName($this->fullEntityClass, 'property_email');
    $calculatedNamePattern = '%'.$propertyText->getTechnicalName().'% %'.$propertyEmail->getTechnicalName().'%';

    $this->fullEntityClass->setCalculatedName($calculatedNamePattern);            

    $this->simpleEntityClass->setParent($this->fullEntityClass);                          

    $calculatedName = $this->entityClassService->initCalculatedName($this->simpleEntityClass);
    $this->assertEquals($calculatedNamePattern, $this->simpleEntityClass->getCalculatedName());
  }

  public function testGetPath()
  {
    $parents = $this->entityClassService->getPath($this->entityClassWithParent);

    $this->assertCount(6, $parents);
    
    for($i=0;$i<=5;$i++) {
      $this->assertEquals('entity_class_'.(15-$i), $parents[$i]->getDisplayName());
    }
  }

  public function testGetEntityClassPath()
  {
    $path = $this->entityClassService->getEntityClassPath($this->entityClassWithParent);

    $this->assertEquals('entity_class_15 -> entity_class_14 -> entity_class_13 -> entity_class_12 -> entity_class_11 -> entity_class_10', $path);
  }
}