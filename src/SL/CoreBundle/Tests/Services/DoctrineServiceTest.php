<?php

namespace SL\CoreBundle\Tests\Services;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DoctrineServiceTest extends WebTestCase
{
	private $doctrineService; 
    private $em; 

    public function setUp()
    {
        $client = static::createClient();
        $this->doctrineService = $client->getContainer()->get('sl_core.doctrine'); 
        $this->em = $client->getContainer()->get('doctrine.orm.entity_manager'); 
    }

    protected function tearDown()
    {
        unset($client, $this->doctrineService, $this->em);
    }

    public function testGenerateEntityFileAndObjectSchema()
    {
        /**
          * #1
          * With parent == null
          */
        //Text property
        $properties = array(); 
        $fieldType = $this->em->getRepository('SLCoreBundle:Field\FieldType')
                                 ->findOneByFormType('text');             

        $property1 = $this->getMock('SL\CoreBundle\Entity\EntityClass\Property');
        $property1->expects($this->any())
                 ->method('getTechnicalName')
                 ->will($this->returnValue('Property1'));
        $property1->expects($this->any())
                 ->method('getFieldType')
                 ->will($this->returnValue($fieldType));
        $property1->expects($this->any())
                 ->method('isMultiple')
                 ->will($this->returnValue(false));
        $property1->expects($this->any())
                 ->method('isRequired')
                 ->will($this->returnValue(true));
        
        $properties[] = $property1; 

        $property2 = $this->getMock('SL\CoreBundle\Entity\EntityClass\Property');
        $property2->expects($this->any())
                 ->method('getTechnicalName')
                 ->will($this->returnValue('Property2'));
        $property2->expects($this->any())
                 ->method('getFieldType')
                 ->will($this->returnValue($fieldType));
        $property2->expects($this->any())
                 ->method('isMultiple')
                 ->will($this->returnValue(false));
        $property2->expects($this->any())
                 ->method('isRequired')
                 ->will($this->returnValue(false));
        
        $properties[] = $property2; 

        //Textarea property
        $fieldType = $this->em->getRepository('SLCoreBundle:Field\FieldType')
                                 ->findOneByFormType('textarea');             

        $property3 = $this->getMock('SL\CoreBundle\Entity\EntityClass\Property');
        $property3->expects($this->any())
                 ->method('getTechnicalName')
                 ->will($this->returnValue('Property3'));
        $property3->expects($this->any())
                 ->method('getFieldType')
                 ->will($this->returnValue($fieldType));
        $property3->expects($this->any())
                 ->method('isMultiple')
                 ->will($this->returnValue(false));
        $property3->expects($this->any())
                 ->method('isRequired')
                 ->will($this->returnValue(true));
        
        $properties[] = $property3; 

        //Email property
        $fieldType = $this->em->getRepository('SLCoreBundle:Field\FieldType')
                                 ->findOneByFormType('email');             

        $property4 = $this->getMock('SL\CoreBundle\Entity\EntityClass\Property');
        $property4->expects($this->any())
                 ->method('getTechnicalName')
                 ->will($this->returnValue('Property4'));
        $property4->expects($this->any())
                 ->method('getFieldType')
                 ->will($this->returnValue($fieldType));
        $property4->expects($this->any())
                 ->method('isMultiple')
                 ->will($this->returnValue(false));
        $property4->expects($this->any())
                 ->method('isRequired')
                 ->will($this->returnValue(true));
        
        $properties[] = $property4; 

        //Money property
        $fieldType = $this->em->getRepository('SLCoreBundle:Field\FieldType')
                                 ->findOneByFormType('money');             

        $property5 = $this->getMock('SL\CoreBundle\Entity\EntityClass\Property');
        $property5->expects($this->any())
                 ->method('getTechnicalName')
                 ->will($this->returnValue('Property5'));
        $property5->expects($this->any())
                 ->method('getFieldType')
                 ->will($this->returnValue($fieldType));
        $property5->expects($this->any())
                 ->method('isMultiple')
                 ->will($this->returnValue(false));
        $property5->expects($this->any())
                 ->method('isRequired')
                 ->will($this->returnValue(true));
        
        $properties[] = $property5; 

        //Number property
        $fieldType = $this->em->getRepository('SLCoreBundle:Field\FieldType')
                                 ->findOneByFormType('number');             

        $property6 = $this->getMock('SL\CoreBundle\Entity\EntityClass\Property');
        $property6->expects($this->any())
                 ->method('getTechnicalName')
                 ->will($this->returnValue('Property6'));
        $property6->expects($this->any())
                 ->method('getFieldType')
                 ->will($this->returnValue($fieldType));
        $property6->expects($this->any())
                 ->method('isMultiple')
                 ->will($this->returnValue(false));
        $property6->expects($this->any())
                 ->method('isRequired')
                 ->will($this->returnValue(true));
        
        $properties[] = $property6; 

        //Percent property
        $fieldType = $this->em->getRepository('SLCoreBundle:Field\FieldType')
                                 ->findOneByFormType('percent');             

        $property7 = $this->getMock('SL\CoreBundle\Entity\EntityClass\Property');
        $property7->expects($this->any())
                 ->method('getTechnicalName')
                 ->will($this->returnValue('Property7'));
        $property7->expects($this->any())
                 ->method('getFieldType')
                 ->will($this->returnValue($fieldType));
        $property7->expects($this->any())
                 ->method('isMultiple')
                 ->will($this->returnValue(false));
        $property7->expects($this->any())
                 ->method('isRequired')
                 ->will($this->returnValue(true));
        
        $properties[] = $property7; 

        //Url property
        $fieldType = $this->em->getRepository('SLCoreBundle:Field\FieldType')
                                 ->findOneByFormType('url');             

        $property8 = $this->getMock('SL\CoreBundle\Entity\EntityClass\Property');
        $property8->expects($this->any())
                 ->method('getTechnicalName')
                 ->will($this->returnValue('Property8'));
        $property8->expects($this->any())
                 ->method('getFieldType')
                 ->will($this->returnValue($fieldType));
        $property8->expects($this->any())
                 ->method('isMultiple')
                 ->will($this->returnValue(false));
        $property8->expects($this->any())
                 ->method('isRequired')
                 ->will($this->returnValue(true));
        
        $properties[] = $property8; 

        //Entity property multiple
        $targetEntityClass1 = $this->getMock('SL\CoreBundle\Entity\EntityClass\EntityClass');
        $targetEntityClass1->expects($this->any())
                    ->method('getTechnicalName')
                    ->will($this->returnValue('TestTargetEntityClass1'));
        $targetEntityClass1->expects($this->any())
                    ->method('getProperties')
                    ->will($this->returnValue(array($property1)));
        $targetEntityClass1->expects($this->any())
                    ->method('getParent')
                    ->will($this->returnValue(null));

        $this->doctrineService->generateEntityFileAndObjectSchema($targetEntityClass1);

        $fieldType = $this->em->getRepository('SLCoreBundle:Field\FieldType')
                                 ->findOneByFormType('entity');             

        $property100 = $this->getMock('SL\CoreBundle\Entity\EntityClass\PropertyEntity');
        $property100->expects($this->any())
                 ->method('getTechnicalName')
                 ->will($this->returnValue('Property100'));
        $property100->expects($this->any())
                 ->method('getFieldType')
                 ->will($this->returnValue($fieldType));
        $property100->expects($this->any())
                 ->method('isMultiple')
                 ->will($this->returnValue(true));
        $property100->expects($this->any())
                 ->method('isRequired')
                 ->will($this->returnValue(true));
        $property100->expects($this->any())
                 ->method('getTargetEntityClass')
                 ->will($this->returnValue($targetEntityClass1));
        
        $properties[] = $property100; 

        //Entity property single
        $targetEntityClass2 = $this->getMock('SL\CoreBundle\Entity\EntityClass\EntityClass');
        $targetEntityClass2->expects($this->any())
                    ->method('getTechnicalName')
                    ->will($this->returnValue('TestTargetEntityClass2'));
        $targetEntityClass2->expects($this->any())
                    ->method('getProperties')
                    ->will($this->returnValue(array($property1)));
        $targetEntityClass2->expects($this->any())
                    ->method('getParent')
                    ->will($this->returnValue(null));

        $this->doctrineService->generateEntityFileAndObjectSchema($targetEntityClass2);

        $fieldType = $this->em->getRepository('SLCoreBundle:Field\FieldType')
                                 ->findOneByFormType('entity');             

        $property101 = $this->getMock('SL\CoreBundle\Entity\EntityClass\PropertyEntity');
        $property101->expects($this->any())
                 ->method('getTechnicalName')
                 ->will($this->returnValue('Property101'));
        $property101->expects($this->any())
                 ->method('getFieldType')
                 ->will($this->returnValue($fieldType));
        $property101->expects($this->any())
                 ->method('isMultiple')
                 ->will($this->returnValue(false));
        $property101->expects($this->any())
                 ->method('isRequired')
                 ->will($this->returnValue(false));
        $property101->expects($this->any())
                 ->method('getTargetEntityClass')
                 ->will($this->returnValue($targetEntityClass2));
        
        $properties[] = $property101; 

        //Choice property multiple
        $choiceList1 = $this->getMock('SL\CoreBundle\Entity\EntityClass\EntityClass');
        $choiceList1->expects($this->any())
                    ->method('getTechnicalName')
                    ->will($this->returnValue('choiceList1'));

        $fieldType = $this->em->getRepository('SLCoreBundle:Field\FieldType')
                                 ->findOneByFormType('choice');

        $property200 = $this->getMock('SL\CoreBundle\Entity\EntityClass\PropertyChoice');
        $property200->expects($this->any())
                 ->method('getTechnicalName')
                 ->will($this->returnValue('Property200'));
        $property200->expects($this->any())
                 ->method('getFieldType')
                 ->will($this->returnValue($fieldType));
        $property200->expects($this->any())
                 ->method('isMultiple')
                 ->will($this->returnValue(true));
        $property200->expects($this->any())
                 ->method('isRequired')
                 ->will($this->returnValue(true));
        $property200->expects($this->any())
                 ->method('getChoiceList')
                 ->will($this->returnValue($choiceList1));
        
        $properties[] = $property200; 

        //Choice property single
        $choiceList2 = $this->getMock('SL\CoreBundle\Entity\EntityClass\EntityClass');
        $choiceList2->expects($this->any())
                    ->method('getTechnicalName')
                    ->will($this->returnValue('choiceList2'));

        $fieldType = $this->em->getRepository('SLCoreBundle:Field\FieldType')
                                 ->findOneByFormType('choice');

        $property201 = $this->getMock('SL\CoreBundle\Entity\EntityClass\PropertyChoice');
        $property201->expects($this->any())
                 ->method('getTechnicalName')
                 ->will($this->returnValue('Property201'));
        $property201->expects($this->any())
                 ->method('getFieldType')
                 ->will($this->returnValue($fieldType));
        $property201->expects($this->any())
                 ->method('isMultiple')
                 ->will($this->returnValue(false));
        $property201->expects($this->any())
                 ->method('isRequired')
                 ->will($this->returnValue(false));
        $property201->expects($this->any())
                 ->method('getChoiceList')
                 ->will($this->returnValue($choiceList2));
        
        $properties[] = $property201; 

        //Date property
        $fieldType = $this->em->getRepository('SLCoreBundle:Field\FieldType')
                                 ->findOneByFormType('genemu_jquerydate');

        $property300 = $this->getMock('SL\CoreBundle\Entity\EntityClass\PropertyChoice');
        $property300->expects($this->any())
                 ->method('getTechnicalName')
                 ->will($this->returnValue('Property300'));
        $property300->expects($this->any())
                 ->method('getFieldType')
                 ->will($this->returnValue($fieldType));
        $property300->expects($this->any())
                 ->method('isMultiple')
                 ->will($this->returnValue(false));
        $property300->expects($this->any())
                 ->method('isRequired')
                 ->will($this->returnValue(false));
        
        $properties[] = $property300; 

        $entityClass1 = $this->getMock('SL\CoreBundle\Entity\EntityClass\EntityClass');
        $entityClass1->expects($this->any())
                    ->method('getTechnicalName')
                    ->will($this->returnValue('TestEntityClass1'));
        $entityClass1->expects($this->any())
                    ->method('getProperties')
                    ->will($this->returnValue($properties));
        $entityClass1->expects($this->any())
                    ->method('getParent')
                    ->will($this->returnValue(null));

        $this->doctrineService->generateEntityFileAndObjectSchema($entityClass1);

        /**
          * #2
          * With not null parent
          */
        $parentEntityClass1 = $this->getMock('SL\CoreBundle\Entity\EntityClass\EntityClass');
        $parentEntityClass1->expects($this->any())
                    ->method('getTechnicalName')
                    ->will($this->returnValue('TestParentEntityClass1'));
        $parentEntityClass1->expects($this->any())
                    ->method('getProperties')
                    ->will($this->returnValue(array($property1)));
        $parentEntityClass1->expects($this->any())
                    ->method('getParent')
                    ->will($this->returnValue(null));

        $this->doctrineService->generateEntityFileAndObjectSchema($parentEntityClass1);

        $entityClass2 = $this->getMock('SL\CoreBundle\Entity\EntityClass\EntityClass');
        $entityClass2->expects($this->any())
                    ->method('getTechnicalName')
                    ->will($this->returnValue('TestEntityClass2'));
        $entityClass2->expects($this->any())
                    ->method('getProperties')
                    ->will($this->returnValue($properties));
        $entityClass2->expects($this->any())
                    ->method('getParent')
                    ->will($this->returnValue($parentEntityClass1));

        $this->doctrineService->generateEntityFileAndObjectSchema($entityClass2);

        $this->assertFileExists('/home/samuel/Sites/symfony/src/SL/DataBundle/Entity/TestEntityClass1.php');
        $this->assertFileExists('/home/samuel/Sites/symfony/src/SL/DataBundle/Entity/TestEntityClass2.php');
        $this->assertFileExists('/home/samuel/Sites/symfony/src/SL/DataBundle/Entity/TestParentEntityClass1.php');
    }

    public function testRemoveEntityFile()
    {
        $properties = array(); 
        $fieldType = $this->em->getRepository('SLCoreBundle:Field\FieldType')
                                 ->findOneByFormType('text');             

        $property1 = $this->getMock('SL\CoreBundle\Entity\EntityClass\Property');
        $property1->expects($this->any())
                 ->method('getTechnicalName')
                 ->will($this->returnValue('Property1'));
        $property1->expects($this->any())
                 ->method('getFieldType')
                 ->will($this->returnValue($fieldType));
        $property1->expects($this->any())
                 ->method('isMultiple')
                 ->will($this->returnValue(false));
        $property1->expects($this->any())
                 ->method('isRequired')
                 ->will($this->returnValue(true));

        $properties[] = $property1;

        $entityClass1 = $this->getMock('SL\CoreBundle\Entity\EntityClass\EntityClass');
        $entityClass1->expects($this->any())
                    ->method('getTechnicalName')
                    ->will($this->returnValue('TestRemoveEntityClass1'));
        $entityClass1->expects($this->any())
                    ->method('getProperties')
                    ->will($this->returnValue($properties));
        $entityClass1->expects($this->any())
                    ->method('getParent')
                    ->will($this->returnValue(null));

        $this->doctrineService->generateEntityFileAndObjectSchema($entityClass1);
        $this->assertFileExists('/home/samuel/Sites/symfony/src/SL/DataBundle/Entity/TestRemoveEntityClass1.php');

        $this->doctrineService->removeEntityFile($entityClass1);
        $this->assertFileNotExists('/home/samuel/Sites/symfony/src/SL/DataBundle/Entity/TestRemoveEntityClass1.php');
    }

    public function testGetDataEntityNamespace()
    { 
       $entityNamespace = $this->doctrineService->getDataEntityNamespace('EntityClass1');

       $this->assertEquals($entityNamespace, 'SL\DataBundle\Entity\EntityClass1'); 
    }

    public function testEntityDelete()
    { 
        /**
          * #1
          * Soft delete
          */
        $entityClass1 = $this->em->getRepository('SLCoreBundle:EntityClass\EntityClass')
                                 ->findOneByDisplayName('testEntityDelete_entityClass1');

        $this->assertNull($entityClass1->getDeletedAt()); 
                                 
        $this->doctrineService->entityDelete($entityClass1);

        $filters = $this->em->getFilters();
        $filters->disable('softdeleteable');

        $entityClass1 = $this->em->getRepository('SLCoreBundle:EntityClass\EntityClass')
                                 ->findOneByDisplayName('testEntityDelete_entityClass1');
            
        $filters->enable('softdeleteable');

        $this->assertEquals('testEntityDelete_entityClass1', $entityClass1->getDisplayName()); 
        $this->assertNotNull($entityClass1->getDeletedAt()); 

        /**
          * #2
          * Hard delete
          */
        $entityClass2 = $this->em->getRepository('SLCoreBundle:EntityClass\EntityClass')
                                 ->findOneByDisplayName('testEntityDelete_entityClass2');

        $this->assertNull($entityClass2->getDeletedAt()); 
                                 
        $this->doctrineService->entityDelete($entityClass2, true);

        $filters = $this->em->getFilters();
        $filters->disable('softdeleteable');

        $entityClass2 = $this->em->getRepository('SLCoreBundle:EntityClass\EntityClass')
                                 ->findOneByDisplayName('testEntityDelete_entityClass2');
            
        $filters->enable('softdeleteable');

        $this->assertNull($entityClass2);
    }
}