<?php

namespace SL\CoreBundle\DataFixtures\ORM\Test;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use SL\CoreBundle\Entity\EntityClass\EntityClass;
use SL\CoreBundle\Entity\EntityClass\PropertyChoice;
use SL\CoreBundle\Entity\Choice\ChoiceList;


class LoadChoiceListServiceTestData extends AbstractFixture implements OrderedFixtureInterface
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
        $choiceList1 = new ChoiceList(); 
        $choiceList1->setDisplayName('testIntegrityControlBeforeDelete_choiceList1'); 
        
        //Test number : 2 
        $choiceList2 = new ChoiceList(); 
        $choiceList2->setDisplayName('testIntegrityControlBeforeDelete_choiceList2'); 

        $fieldType = $manager->getRepository('SLCoreBundle:Field\FieldType')->findOneByFormType('choice');
        $propertyChoice1 = new PropertyChoice($fieldType); 
        $propertyChoice1->setDisplayName('testIntegrityControlBeforeDelete_propertyChoice1'); 
        $propertyChoice1->setChoiceList($choiceList2); 

        $entityClass1 = new EntityClass(); 
        $entityClass1->setDisplayName('testIntegrityControlBeforeDelete_entityClass1'); 
        $entityClass1->addProperty($propertyChoice1); 
        $propertyChoice1->setEntityClass($entityClass1); 

        $manager->persist($choiceList1);
        $manager->persist($choiceList2);
        $manager->persist($entityClass1);

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