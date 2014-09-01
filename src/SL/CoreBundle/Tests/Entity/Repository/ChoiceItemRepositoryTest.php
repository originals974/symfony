<?php

namespace SL\CoreBundle\Tests\Services;

use Liip\FunctionalTestBundle\Test\WebTestCase;

class ChoiceItemRepositoryTest extends WebTestCase
{
    private $choiceListRepository;
    private $choiceList;

    public function setUp()
    {
        //Load fixtures
        $classes = array(
            'SL\CoreBundle\DataFixtures\ORM\Test\LoadChoiceItemRepositoryTestData',
        );
        $this->loadFixtures($classes);

        $em = $this->getContainer()->get('doctrine')->getManager(); 
        $this->choiceListRepository = $em->getRepository('SLCoreBundle:Choice\ChoiceItem'); 
        $this->choiceList = $em->getRepository('SLCoreBundle:Choice\ChoiceList')->findOneByDisplayName('choice_list_1'); 
    }

    protected function tearDown()
    {
       unset($this->choiceListRepository, $this->choiceList); 
    }

    public function testFullFindByChoiceList()
    {
        $items = $this->choiceListRepository->fullFindByChoiceList($this->choiceList);

        $this->assertCount(10, $items);
    }
}