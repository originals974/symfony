<?php

namespace SL\CoreBundle\DataFixtures\ORM\Test;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadChoiceItemServiceTestData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {   
        $testService = $this->container->get('sl_core.test');

        $choiceList = $testService->getChoiceListInstance(1,5);
        
        $manager->persist($choiceList);  
        $manager->flush(); 
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 120; 
    }
}