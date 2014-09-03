<?php

namespace SL\CoreBundle\Tests\Services;

use Liip\FunctionalTestBundle\Test\WebTestCase;

class ChoiceListRepositoryTest extends WebTestCase
{
    private $choiceListRepository;
    private $choiceList;

    public function setUp()
    {
        //Load fixtures
        $classes = array(
            'SL\CoreBundle\DataFixtures\ORM\Test\LoadChoiceListRepositoryTestData',
        );
        $this->loadFixtures($classes);

        $em = $this->getContainer()->get('doctrine')->getManager(); 
        $this->choiceListRepository = $em->getRepository('SLCoreBundle:Choice\ChoiceList'); 
        $this->choiceList = $em->getRepository('SLCoreBundle:Choice\ChoiceList')->findOneByDisplayName('choice_list_1'); 
    }

    protected function tearDown()
    {
       unset($this->choiceListRepository, $this->choiceList); 
    }

    public function testFullFindAll()
    {
        $choiceLists = $this->choiceListRepository->fullFindAll(); 
        $this->assertCount(10, $choiceLists); 
    }

    public function testFullFindById()
    {
        $choiceList = $this->choiceListRepository->fullFindById($this->choiceList->getId()); 
        
        $this->assertInstanceOf('SL\CoreBundle\Entity\Choice\ChoiceList', $choiceList);
    }
}