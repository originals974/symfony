<?php

namespace SL\CoreBundle\DataFixtures\ORM\Test;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use SL\CoreBundle\Entity\EntityClass\EntityClass;
use SL\CoreBundle\Entity\EntityClass\PropertyEntity;
use SL\CoreBundle\Entity\EntityClass\Property;

class LoadEntityClassServiceTestData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        /**
        * Function tested : testIntegrityControlBeforeDelete
        */ 
        //Test number : 1 
        $entityClass1 = new EntityClass(); 
        $entityClass1->setDisplayName('testIntegrityControlBeforeDelete_entityClass1'); 
        
        //Test number : 2 
        $entityClass2 = new EntityClass(); 
        $entityClass2->setDisplayName('testIntegrityControlBeforeDelete_entityClass2'); 

        $entityClass3 = new EntityClass(); 
        $entityClass3->setDisplayName('testIntegrityControlBeforeDelete_entityClass3'); 

        $fieldType = $manager->getRepository('SLCoreBundle:Field\FieldType')->findOneByFormType('entity');
        $entityProperty = new PropertyEntity($fieldType);
        $entityProperty->setDisplayName('testIntegrityControlBeforeDelete_entityProperty1'); 
        $entityProperty->setTargetEntityClass($entityClass2); 

        $entityClass3->addProperty($entityProperty); 
        $entityProperty->setEntityClass($entityClass3); 

        $manager->persist($entityClass1);
        $manager->persist($entityClass2);
        $manager->persist($entityClass3);

        /**
        * Function tested : testGetPath
        */ 
        $fieldType = $manager->getRepository('SLCoreBundle:Field\FieldType')->findOneByFormType('text');
        
        $parentEntityClass = null; 
        for($i=0;$i<5;$i++) {
            $entityClass = new EntityClass(); 
            $entityClass->setDisplayName('testGetPath_entityClass'.$i); 

            if($parentEntityClass){
                $entityClass->setParent($parentEntityClass); 
            }

            for($j=0;$j<5;$j++) {
                $entityProperty = new Property($fieldType);
                $entityProperty->setDisplayName('testGetPath_property'.$j); 

                $entityClass->addProperty($entityProperty); 
                $entityProperty->setEntityClass($entityClass); 
            }

            $manager->persist($entityClass);

            $parentEntityClass = $entityClass; 
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