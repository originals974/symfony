<?php

namespace SL\CoreBundle\DataFixtures\ORM\Test;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadEntityClassServiceTestData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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

        $fullEntityClass = $testService->getEntityClassInstance($manager);
        $simpleEntityClass = $testService->getSimpleEntityClassInstance();  
        $entityClassesWithParents = $testService->getSimpleEntityClassWithParentsInstance(10,5);

        $manager->persist($fullEntityClass); 
        $manager->persist($simpleEntityClass); 

        foreach($entityClassesWithParents as $entityClassWithParents){
            $manager->persist($entityClassWithParents); 
        }

        $manager->flush(); 
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 100; 
    }
}