<?php

namespace SL\CoreBundle\Tests\Services\Choice;

use Liip\FunctionalTestBundle\Test\WebTestCase; 

class ChoiceItemServiceTest extends WebTestCase
{
	private $choiceItemService; 
  private $choiceList;
  private $em; 

  public function setUp()
  {
    $this->choiceItemService = $this->getContainer()->get('sl_core.choice_item'); 
    $this->em = $this->getContainer()->get('doctrine.orm.entity_manager'); 

    $classes = array(
        'SL\CoreBundle\DataFixtures\ORM\Base\LoadFieldTypeData',
        'SL\CoreBundle\DataFixtures\ORM\Test\LoadChoiceItemServiceTestData',
    );
    $this->loadFixtures($classes);

    $this->choiceList = $this->em->getRepository('SLCoreBundle:Choice\ChoiceList')
                                 ->findOneByDisplayName('choice_list_1'); 
  }

  protected function tearDown()
	{
	  unset(
      $this->choiceItemService,
      $this->choiceList,
      $this->em
      );
	}

  public function testCreateCreateForm()
  {
    $form = $this->choiceItemService->createCreateForm($this->choiceList->getChoiceItems()->first());

   	$this->assertInstanceOf('Symfony\Component\Form\Form', $form);
  }

  public function testCreateEditForm()
  {
    $form = $this->choiceItemService->createEditForm($this->choiceList->getChoiceItems()->first());

   	$this->assertInstanceOf('Symfony\Component\Form\Form', $form);
  }

  public function testCreateDeleteForm()
  {
    $form = $this->choiceItemService->createDeleteForm($this->choiceList->getChoiceItems()->first());

   	$this->assertInstanceOf('Symfony\Component\Form\Form', $form);
  }
}