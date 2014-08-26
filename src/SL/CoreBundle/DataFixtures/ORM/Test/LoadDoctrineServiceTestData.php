<?php

namespace SL\CoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use SL\CoreBundle\Entity\EntityClass\EntityClass;
use SL\CoreBundle\Entity\EntityClass\Property;

class LoadDoctrineServiceTestData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        /**
        * Function tested : testEntityDelete
        */ 
        //Test number : 1 
        $entityClass1 = new EntityClass(); 
        $entityClass1->setDisplayName('testEntityDelete_entityClass1'); 

        $fieldType = $manager->getRepository('SLCoreBundle:Field\FieldType')->findOneByFormType('text');
        $property1 = new Property($fieldType);
        $property1->setDisplayName('testEntityDelete_property1'); 

        $entityClass1->addProperty($property1); 
        $property1->setEntityClass($entityClass1); 

        $manager->persist($entityClass1);

        //Test number : 2
        $entityClass2 = new EntityClass(); 
        $entityClass2->setDisplayName('testEntityDelete_entityClass2'); 

        $fieldType = $manager->getRepository('SLCoreBundle:Field\FieldType')->findOneByFormType('text');
        $property2 = new Property($fieldType);
        $property2->setDisplayName('testEntityDelete_property2'); 

        $entityClass2->addProperty($property2); 
        $property2->setEntityClass($entityClass2); 

        $manager->persist($entityClass2);

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 110; 
    }
}