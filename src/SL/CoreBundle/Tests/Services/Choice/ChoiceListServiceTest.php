<?php

namespace SL\CoreBundle\Tests\Services\Choice;

use Liip\FunctionalTestBundle\Test\WebTestCase; 

class ChoiceListServiceTest extends WebTestCase
{
	private $choiceListService; 
	private $em; 
	private $translator;

  public function setUp()
  {
    $this->choiceListService = $this->getContainer()->get('sl_core.choice_list'); 
    $this->em = $this->getContainer()->get('doctrine.orm.entity_manager'); 
    $this->translator = $this->getContainer()->get('translator'); 

    $classes = array(
        'SL\CoreBundle\DataFixtures\ORM\LoadFieldTypeData',
        'SL\CoreBundle\DataFixtures\ORM\Test\LoadChoiceListServiceTestData',
    );
    $this->loadFixtures($classes);
  }

  protected function tearDown()
	{
	  unset($this->choiceListService, $this->em, $this->translator);
	}

  public function testCreateCreateForm()
  {
  	$choiceList = $this->getMock('SL\CoreBundle\Entity\Choice\ChoiceList');

    $form = $this->choiceListService->createCreateForm($choiceList);
   	$this->assertInstanceOf('Symfony\Component\Form\Form', $form);
  }

  public function testCreateEditForm()
  {
  	$choiceList = $this->getMock('SL\CoreBundle\Entity\Choice\ChoiceList');
  	$choiceList->expects($this->once())
          		->method('getId')
          		->will($this->returnValue(1));

    $form = $this->choiceListService->createEditForm($choiceList);
   	$this->assertInstanceOf('Symfony\Component\Form\Form', $form);
  }

  public function testCreateDeleteForm()
  {
  	$choiceList = $this->getMock('SL\CoreBundle\Entity\Choice\ChoiceList');
  	$choiceList->expects($this->once())
          		->method('getId')
          		->will($this->returnValue(1));

    $form = $this->choiceListService->createDeleteForm($choiceList);
   	$this->assertInstanceOf('Symfony\Component\Form\Form', $form);
  }

  public function testIntegrityControlBeforeDelete()
  {
  	/**
      * #1
      * Not linked to property choice
      */
  	$propertyChoice1 = $this->em->getRepository('SLCoreBundle:Choice\ChoiceList')
  							                ->findOneByDisplayName('testIntegrityControlBeforeDelete_choiceList1'); 

    $integrityError = $this->choiceListService->integrityControlBeforeDelete($propertyChoice1);
   	$this->assertNull($integrityError);

   	/**
    * #2
    * Linked to property choice
    */
    $propertyChoice2 = $this->em->getRepository('SLCoreBundle:Choice\ChoiceList')
							                  ->findOneByDisplayName('testIntegrityControlBeforeDelete_choiceList2'); 

    $integrityError = $this->choiceListService->integrityControlBeforeDelete($propertyChoice2);
   	
    $expectedIntegrityError = array(
        'title' => $this->translator->trans('delete.error.title'),
        'message' => $this->translator->trans('choice_list.delete.reference.error.message')
        );

   	$this->assertEquals($expectedIntegrityError, $integrityError);
  }
}