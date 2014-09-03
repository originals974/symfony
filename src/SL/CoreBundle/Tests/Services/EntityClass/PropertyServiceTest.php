<?php

namespace SL\CoreBundle\Tests\Services\EntityClass;

use Liip\FunctionalTestBundle\Test\WebTestCase;

class PropertyServiceTest extends WebTestCase
{
	private $propertyService; 
  private $testService; 
	private $em; 
	private $translator;
  private $fullEntityClass;
  private $propertyText;
  private $propertyEntity;
  private $propertyChoice;

  public function setUp()
  {
    $this->propertyService = $this->getContainer()->get('sl_core.property'); 
    $this->testService = $this->getContainer()->get('sl_core.test'); 
    $this->em = $this->getContainer()->get('doctrine.orm.entity_manager'); 
    $this->translator = $this->getContainer()->get('translator'); 

    $classes = array(
        'SL\CoreBundle\DataFixtures\ORM\Base\LoadFieldTypeData',
        'SL\CoreBundle\DataFixtures\ORM\Test\LoadPropertyServiceTestData',
    );

    $this->fullEntityClass = $this->em->getRepository('SLCoreBundle:EntityClass\EntityClass')
                                      ->findOneByDisplayName('entity_class'); 

    $this->propertyText = $this->testService->getPropertyByDisplayName($this->fullEntityClass, 'property_text'); 
    $this->propertyEntity = $this->testService->getPropertyByDisplayName($this->fullEntityClass, 'property_entity'); 
    $this->propertyChoice = $this->testService->getPropertyByDisplayName($this->fullEntityClass, 'property_choice'); 
  }

  protected function tearDown()
	{
	  unset(
      $this->propertyService, 
      $this->testService,
      $this->em, 
      $this->translator,
      $this->fullEntityClass,
      $this->propertyText,
      $this->propertyEntity, 
      $this->propertyChoice
      );
	}

  public function testCreateCreateForm()
  {
    $form = $this->propertyService->createCreateForm($this->propertyText);
    
    $this->assertCount(2, $form);

    $this->assertArrayHasKey('selectForm', $form);
    $this->assertArrayHasKey('mainForm', $form);

    $this->assertInstanceOf('Symfony\Component\Form\Form', $form['selectForm']);
    $this->assertInstanceOf('Symfony\Component\Form\Form', $form['mainForm']);
  }

  public function testCreateEditForm()
  {
    /**
    * #1
    * For text property
    */
    $form = $this->propertyService->createEditForm($this->propertyText);
   
    $this->assertInstanceOf('Symfony\Component\Form\Form', $form);

    /**
    * #2
    * For entity property
    */
    $form = $this->propertyService->createEditForm($this->propertyEntity);
   
    $this->assertInstanceOf('Symfony\Component\Form\Form', $form);

    /**
    * #3
    * For choice property
    */
    $form = $this->propertyService->createEditForm($this->propertyChoice);
   
    $this->assertInstanceOf('Symfony\Component\Form\Form', $form);
  }

  public function testCreateDeleteForm()
  {
    $form = $this->propertyService->createDeleteForm($this->propertyText);
   
    $this->assertInstanceOf('Symfony\Component\Form\Form', $form);
  }

  public function testIntegrityControlBeforeDelete()
  {
    /**
    * #1
    * Not used in calculated name
    */
    $integrityError = $this->propertyService->integrityControlBeforeDelete($this->propertyText);
    $this->assertNull($integrityError);

    /**
    * #2
    * Used in calculated name
    */
    $this->fullEntityClass->setCalculatedName('%'.$this->propertyText->getTechnicalName().'%'); 

    $integrityError = $this->propertyService->integrityControlBeforeDelete($this->propertyText);
    $expectedIntegrityError = array(
        'title' => $this->translator->trans('delete.error.title'),
        'message' => $this->translator->trans('property.delete.calculated_name.error.message')
        );

    $this->assertEquals($expectedIntegrityError, $integrityError);
  }
}