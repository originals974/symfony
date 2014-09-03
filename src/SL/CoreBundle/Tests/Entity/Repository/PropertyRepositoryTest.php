<?php

namespace SL\CoreBundle\Tests\Services;

use Liip\FunctionalTestBundle\Test\WebTestCase;

class PropertyRepositoryTest extends WebTestCase
{
    private $testService;
    private $propertyRepository;
    private $entityClass;

    public function setUp()
    {
        //Load fixtures
        $classes = array(
            'SL\CoreBundle\DataFixtures\ORM\Base\LoadFieldTypeData',
            'SL\CoreBundle\DataFixtures\ORM\Test\LoadPropertyRepositoryTestData',
        );
        $this->loadFixtures($classes);

        $this->testService = $this->getContainer()->get('sl_core.test'); 
        $em = $this->getContainer()->get('doctrine')->getManager(); 

        $this->propertyRepository = $em->getRepository('SLCoreBundle:EntityClass\Property'); 
        $this->entityClass = $em->getRepository('SLCoreBundle:EntityClass\EntityClass')->findOneByDisplayName('entity_class');         
    }

    protected function tearDown()
    {
       unset($this->testService, $this->propertyRepository, $this->entityClass); 
    }

    public function testFindByEntityClassAndDisplayName()
    { 
        $propertyText = $this->testService->getPropertyByDisplayName($this->entityClass, 'property_text'); 

        $property = $this->propertyRepository->findByEntityClassAndDisplayName(array(
            'entityClass' => $this->entityClass->getId(), 
            'displayName' => $propertyText->getDisplayName(),
            )
        );

        $this->assertCount(1, $property);
    }

    public function testFindPropertyEntityByEntityClass()
    {
        $properties =$this->propertyRepository->findPropertyEntityByEntityClass($this->entityClass); 

        $this->assertCount(2, $properties); 

    }
}