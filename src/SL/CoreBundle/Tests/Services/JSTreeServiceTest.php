<?php

namespace SL\CoreBundle\Tests\Services;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class JSTreeServiceTest extends WebTestCase
{
	private $jsTreeService; 

	public function setUp()
    {
    	$client = static::createClient();
    	$this->jsTreeService = $client->getContainer()->get('sl_core.js_tree'); 
    }

    protected function tearDown()
	{
	    unset($client, $this->jsTreeService);
	}

    public function testCreateNewEntityClassNode()
    {
    	$entityClass = $this->getMock('SL\CoreBundle\Entity\EntityClass\EntityClass');

    	$entityClass->expects($this->once())
            		->method('getId')
            		->will($this->returnValue(1));

        $entityClass->expects($this->once())
            		->method('getTechnicalName')
            		->will($this->returnValue('EntityClass1'));
        
        $entityClass->expects($this->once())
            		->method('getDisplayName')
            		->will($this->returnValue('Entity Class 1'));

        $entityClass->expects($this->once())
            		->method('getIcon')
            		->will($this->returnValue('fa-question'));

    	$newNode = $this->jsTreeService->createNewEntityClassNode($entityClass); 
    	
        $testArray = array(
            'id' => 'EntityClass1',
            'text' => 'Entity Class 1',
            'icon' => 'fa fa-question',
            'a_attr' => array(
                'href' => '/back_end/entity_class/1/show' 
                ),
        );

        $this->assertEquals($testArray, $newNode);
    }

    public function testcreateNewChoiceListNode()
    {
    	$choiceList = $this->getMock('SL\CoreBundle\Entity\Choice\ChoiceList');

    	$choiceList->expects($this->once())
            		->method('getId')
            		->will($this->returnValue(1));

        $choiceList->expects($this->once())
            		->method('getTechnicalName')
            		->will($this->returnValue('ChoiceList1'));
        
        $choiceList->expects($this->once())
            		->method('getDisplayName')
            		->will($this->returnValue('Choice List 1'));

    	$newNode = $this->jsTreeService->createNewChoiceListNode($choiceList); 
    	
        $testArray = array(
            'id' => 'ChoiceList1',
            'text' => 'Choice List 1',
            'icon' => 'fa fa-list',
            'a_attr' => array(
                'href' => '/back_end/choice_list/1/show' 
                ),
        );

        $this->assertEquals($testArray, $newNode);
    }

    public function testshortenTextNode()
    {
    	/**
        * #1
        * $textToShorten < $maxLength
        */
    	$textToShorten = 'text_test'; 
    	$shortedText = $this->jsTreeService->shortenTextNode($textToShorten, 10);
        $this->assertEquals($textToShorten, $shortedText);

        /**
        * #2
        * $textToShorten = $maxLength
        */
        $textToShorten = 'long__text'; 
    	$shortedText = $this->jsTreeService->shortenTextNode($textToShorten, 10);
        $this->assertEquals($textToShorten, $shortedText);

        /**
        * #3
        * $textToShorten > $maxLength
        */
        $textToShorten = 'long___text'; 
    	$shortedText = $this->jsTreeService->shortenTextNode($textToShorten, 10);
    	$this->assertStringStartsWith('long_', $shortedText);
    	$this->assertStringEndsWith('_text', $shortedText);
    	$this->assertContains('.....', $shortedText);
    }
}
