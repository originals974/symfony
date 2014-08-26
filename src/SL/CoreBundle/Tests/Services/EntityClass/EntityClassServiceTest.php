<?php

namespace SL\CoreBundle\Tests\Services;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use SL\CoreBundle\Services\EntityClass\EntityClassService; 

class EntityClassServiceTest extends WebTestCase
{
	private $entityClassService; 
	private $em; 
	private $translator;

  public function setUp()
  {
    $client = static::createClient();
    $this->entityClassService = $client->getContainer()->get('sl_core.entity_class'); 
    $this->em = $client->getContainer()->get('doctrine.orm.entity_manager'); 
    $this->translator = $client->getContainer()->get('translator'); 
  }

  protected function tearDown()
	{
	  unset($client, $this->entityClassService, $this->em, $this->translator);
	}

  public function testCreateCreateForm()
  {
  	/**
      * #1
      * With parent == null
      */
  	$entityClass1 = $this->getMock('SL\CoreBundle\Entity\EntityClass\EntityClass');
  	$entityClass1->expects($this->once())
          		->method('getParent')
          		->will($this->returnValue(null));

    $form = $this->entityClassService->createCreateForm($entityClass1);
   	$this->assertInstanceOf('Symfony\Component\Form\Form', $form);
   	
   	/**
    * #2
    * With parent != null
    */
   	$parentEntityClass = $this->getMock('SL\CoreBundle\Entity\EntityClass\EntityClass');
   	$parentEntityClass->expects($this->once())
        			  ->method('getId')
        			  ->will($this->returnValue(1));

    $entityClass2 = $this->getMock('SL\CoreBundle\Entity\EntityClass\EntityClass');
   	$entityClass2->expects($this->once())
        		 ->method('getParent')
        		 ->will($this->returnValue($parentEntityClass));

    $form = $this->entityClassService->createCreateForm($entityClass2);
   	$this->assertInstanceOf('Symfony\Component\Form\Form', $form);

  }

  public function testCreateEditForm()
  {
  	$entityClass = $this->getMock('SL\CoreBundle\Entity\EntityClass\EntityClass');
  	$entityClass->expects($this->once())
          		->method('getId')
          		->will($this->returnValue(1));

    $form = $this->entityClassService->createEditForm($entityClass);
   	$this->assertInstanceOf('Symfony\Component\Form\Form', $form);
  }

  public function testCreateDeleteForm()
  {
  	$entityClass = $this->getMock('SL\CoreBundle\Entity\EntityClass\EntityClass');
  	$entityClass->expects($this->once())
          		->method('getId')
          		->will($this->returnValue(1));

    $form = $this->entityClassService->createDeleteForm($entityClass);
   	$this->assertInstanceOf('Symfony\Component\Form\Form', $form);
  }

  public function testCreateEditCalculatedNameForm()
  {
  	$entityClass = $this->getMock('SL\CoreBundle\Entity\EntityClass\EntityClass');
  	$entityClass->expects($this->once())
          		->method('getId')
          		->will($this->returnValue(1));

    $form = $this->entityClassService->createEditCalculatedNameForm($entityClass);
   	$this->assertInstanceOf('Symfony\Component\Form\Form', $form);
  }

  public function testIntegrityControlBeforeDelete()
  {
  	/**
      * #1
      * Not linked to other entity class
      */
  	$entityClass1 = $this->em->getRepository('SLCoreBundle:EntityClass\EntityClass')
  							 ->findOneByDisplayName('testIntegrityControlBeforeDelete_entityClass1'); 

    $integrityError = $this->entityClassService->integrityControlBeforeDelete($entityClass1);
   	$this->assertNull($integrityError);

   	/**
    * #2
    * Linked to other entity class
    */
    $entityClass2 = $this->em->getRepository('SLCoreBundle:EntityClass\EntityClass')
							 ->findOneByDisplayName('testIntegrityControlBeforeDelete_entityClass2'); 

    $integrityError = $this->entityClassService->integrityControlBeforeDelete($entityClass2);
   	
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
    $property = $this->getMock('SL\CoreBundle\Entity\EntityClass\Property');
    $property->expects($this->once())
             ->method('getTechnicalName')
             ->will($this->returnValue('Property11'));

    $properties = $this->getMock('Doctrine\Common\Collections\ArrayCollection');
    $properties->expects($this->once())
               ->method('first')
               ->will($this->returnValue($property));

    $entityClass1 = $this->getMock('SL\CoreBundle\Entity\EntityClass\EntityClass');
    $entityClass1->expects($this->any())
                 ->method('getParent')
                 ->will($this->returnValue(null));           
    $entityClass1->expects($this->once())
                 ->method('getProperties')
                 ->will($this->returnValue($properties));

    $calculatedName = $this->entityClassService->initCalculatedName($entityClass1);
    $this->assertEquals('%Property11%', $calculatedName);

    /**
    * #2
    * Object with parent
    */
    $calculatedNamePattern = '%Property12% %Property14%(%Property4%)'; 

    $parentEntityClass = $this->getMock('SL\CoreBundle\Entity\EntityClass\EntityClass');
    $parentEntityClass->expects($this->once())
                      ->method('getCalculatedName')
                      ->will($this->returnValue($calculatedNamePattern)); 

    $entityClass2 = $this->getMock('SL\CoreBundle\Entity\EntityClass\EntityClass');
    $entityClass2->expects($this->any())
                 ->method('getParent')
                 ->will($this->returnValue($parentEntityClass)); 

    $calculatedName = $this->entityClassService->initCalculatedName($entityClass2);
    $this->assertEquals($calculatedNamePattern, $calculatedName);
  }

  public function testGetPath()
  {
    $entityClass = $this->em->getRepository('SLCoreBundle:EntityClass\EntityClass')
                        ->findOneByDisplayName('testGetPath_entityClass4'); 


    $parents = $this->entityClassService->getPath($entityClass);

    $this->assertCount(5, $parents);
    
    for($i=0;$i<5;$i++) {
      $this->assertEquals('testGetPath_entityClass'.$i, $parents[$i]->getDisplayName());
    }
  }

  public function testGetEntityClassPath()
  {
    $entityClass = $this->em->getRepository('SLCoreBundle:EntityClass\EntityClass')
                        ->findOneByDisplayName('testGetPath_entityClass4'); 


    $path = $this->entityClassService->getEntityClassPath($entityClass);

    $this->assertEquals('testGetPath_entityClass0 -> testGetPath_entityClass1 -> testGetPath_entityClass2 -> testGetPath_entityClass3 -> testGetPath_entityClass4', $path);
  }
}