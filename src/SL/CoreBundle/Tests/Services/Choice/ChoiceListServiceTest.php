<?php

namespace SL\CoreBundle\Tests\Services\Choice;

use Liip\FunctionalTestBundle\Test\WebTestCase; 

class ChoiceListServiceTest extends WebTestCase
{
	private $choiceListService; 
	private $testService; 
  private $em; 
	private $translator;
  private $choiceList; 
  private $fullEntityClass; 
  private $propertyChoice; 

  public function setUp()
  {
    $this->choiceListService = $this->getContainer()->get('sl_core.choice_list'); 
    $this->testService = $this->getContainer()->get('sl_core.test'); 

    $this->em = $this->getContainer()->get('doctrine.orm.entity_manager'); 
    $this->translator = $this->getContainer()->get('translator'); 

    $classes = array(
        'SL\CoreBundle\DataFixtures\ORM\Base\LoadFieldTypeData',
        'SL\CoreBundle\DataFixtures\ORM\Test\LoadChoiceListServiceTestData',
    );
    $this->loadFixtures($classes);

    $this->choiceList = $this->em->getRepository('SLCoreBundle:Choice\ChoiceList')
                                 ->findOneByDisplayName('choice_list_1'); 

    $this->fullEntityClass = $this->em->getRepository('SLCoreBundle:EntityClass\EntityClass')
                                      ->findOneByDisplayName('entity_class'); 

    $this->propertyChoice = $this->testService->getPropertyByDisplayName($this->fullEntityClass, 'property_choice');
  }

  protected function tearDown()
	{
	  unset(
      $this->choiceListService, 
      $this->testService,
      $this->em, 
      $this->translator,
      $this->choiceList,
      $this->fullEntityClass,
      $this->propertyChoice
      );
	}

  public function testCreateCreateForm()
  {
    $form = $this->choiceListService->createCreateForm($this->choiceList);
   	$this->assertInstanceOf('Symfony\Component\Form\Form', $form);
  }

  public function testCreateEditForm()
  {
    $form = $this->choiceListService->createEditForm($this->choiceList);
   	$this->assertInstanceOf('Symfony\Component\Form\Form', $form);
  }

  public function testCreateDeleteForm()
  {
    $form = $this->choiceListService->createDeleteForm($this->choiceList);
   	$this->assertInstanceOf('Symfony\Component\Form\Form', $form);
  }

  public function testIntegrityControlBeforeDelete()
  {
  	/**
    * #1
    * Not linked to property choice
    */
    $integrityError = $this->choiceListService->integrityControlBeforeDelete($this->choiceList);
   	$this->assertNull($integrityError);

   	/**
    * #2
    * Linked to property choice
    */
    $integrityError = $this->choiceListService->integrityControlBeforeDelete($this->propertyChoice->getChoiceList());
   	
    $expectedIntegrityError = array(
        'title' => $this->translator->trans('delete.error.title'),
        'message' => $this->translator->trans('choice_list.delete.reference.error.message')
        );

   	$this->assertEquals($expectedIntegrityError, $integrityError);
  }
}