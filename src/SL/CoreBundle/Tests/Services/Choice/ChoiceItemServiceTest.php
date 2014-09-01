<?php

namespace SL\CoreBundle\Tests\Services\Choice;

use Liip\FunctionalTestBundle\Test\WebTestCase; 

class ChoiceItemServiceTest extends WebTestCase
{
	private $choiceItemService; 

  public function setUp()
  {
    $this->choiceItemService = $this->getContainer()->get('sl_core.choice_item'); 
  }

  protected function tearDown()
	{
	  unset($this->choiceItemService);
	}

  public function testCreateCreateForm()
  {
    $choiceList = $this->getMock('SL\CoreBundle\Entity\Choice\ChoiceList');
    $choiceList->expects($this->once())
              ->method('getId')
              ->will($this->returnValue(1));

  	$choiceItem = $this->getMock('SL\CoreBundle\Entity\Choice\ChoiceItem');
    $choiceItem->expects($this->once())
              ->method('getChoiceList')
              ->will($this->returnValue($choiceList));

    $form = $this->choiceItemService->createCreateForm($choiceItem);

   	$this->assertInstanceOf('Symfony\Component\Form\Form', $form);
  }

  public function testCreateEditForm()
  {
  	$choiceList = $this->getMock('SL\CoreBundle\Entity\Choice\ChoiceList');
    $choiceList->expects($this->once())
              ->method('getId')
              ->will($this->returnValue(1));

    $choiceItem = $this->getMock('SL\CoreBundle\Entity\Choice\ChoiceItem');
    $choiceItem->expects($this->once())
              ->method('getId')
              ->will($this->returnValue(1));
    $choiceItem->expects($this->once())
              ->method('getChoiceList')
              ->will($this->returnValue($choiceList));
    $form = $this->choiceItemService->createEditForm($choiceItem);

   	$this->assertInstanceOf('Symfony\Component\Form\Form', $form);
  }

  public function testCreateDeleteForm()
  {
  	$choiceList = $this->getMock('SL\CoreBundle\Entity\Choice\ChoiceList');
    $choiceList->expects($this->once())
              ->method('getId')
              ->will($this->returnValue(1));

    $choiceItem = $this->getMock('SL\CoreBundle\Entity\Choice\ChoiceItem');
    $choiceItem->expects($this->once())
              ->method('getId')
              ->will($this->returnValue(1));
    $choiceItem->expects($this->once())
              ->method('getChoiceList')
              ->will($this->returnValue($choiceList));
    $form = $this->choiceItemService->createDeleteForm($choiceItem);

   	$this->assertInstanceOf('Symfony\Component\Form\Form', $form);
  }
}