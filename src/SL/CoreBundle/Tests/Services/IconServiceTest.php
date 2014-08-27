<?php

namespace SL\CoreBundle\Tests\Services;

use Liip\FunctionalTestBundle\Test\WebTestCase;

class IconServiceTest extends WebTestCase
{
	private $iconService; 

    public function setUp()
    {
        $this->iconService = $this->getContainer()->get('sl_core.icon'); 
    }


    protected function tearDown()
    {
        unset($this->iconService);
    }

    public function testGetRootServerIcon()
    {
        /**
        * #1
        * Without option
        */
        $iconName = $this->iconService->getRootServerIcon();
        $this->assertEquals('fa fa-database', $iconName);

        /**
        * #2
        * Without option
        */
        $iconName = $this->iconService->getRootServerIcon('fa-lg');
        $this->assertEquals('fa fa-database fa-lg', $iconName);
    }

    public function testGetEntityClassIcon()
    {
    	$entityClass = $this->getMock('SL\CoreBundle\Entity\EntityClass\EntityClass');
        $entityClass->expects($this->once())
                    ->method('getIcon')
                    ->will($this->returnValue('fa-question'));

        $iconName = $this->iconService->getEntityClassIcon($entityClass);
        $this->assertEquals('fa fa-question', $iconName);
    }

    public function testGetRootEntityClassIcon()
    {
        /**
        * #1
        * Without option
        */
        $iconName = $this->iconService->getRootEntityClassIcon();
        $this->assertEquals('fa fa-archive', $iconName);

        /**
        * #2
        * Without option
        */
        $iconName = $this->iconService->getRootEntityClassIcon('fa-lg');
        $this->assertEquals('fa fa-archive fa-lg', $iconName);
    }

    public function testGetRootChoiceListIcon()
    {
        /**
        * #1
        * Without option
        */
        $iconName = $this->iconService->getRootChoiceListIcon();
        $this->assertEquals('fa fa-list', $iconName);

        /**
        * #2
        * Without option
        */
        $iconName = $this->iconService->getRootChoiceListIcon('fa-lg');
        $this->assertEquals('fa fa-list fa-lg', $iconName);
    }

    public function testGetChoiceListIcon()
    {
        /**
        * #1
        * Without option
        */
        $iconName = $this->iconService->getChoiceListIcon();
        $this->assertEquals('fa fa-list', $iconName);

        /**
        * #2
        * Without option
        */
        $iconName = $this->iconService->getChoiceListIcon('fa-lg');
        $this->assertEquals('fa fa-list fa-lg', $iconName);
    }

    public function testGetChoiceItemIcon()
    {
    	$choiceItem = $this->getMock('SL\CoreBundle\Entity\Choice\ChoiceItem');
        $choiceItem->expects($this->once())
                    ->method('getIcon')
                    ->will($this->returnValue('fa-minus'));

        $iconName = $this->iconService->getChoiceItemIcon($choiceItem);
        $this->assertEquals('fa fa-minus', $iconName);
    }
}