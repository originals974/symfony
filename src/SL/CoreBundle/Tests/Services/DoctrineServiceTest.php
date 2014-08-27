<?php

namespace SL\CoreBundle\Tests\Services;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Doctrine\ORM\Tools\SchemaTool;

class DoctrineServiceTest extends WebTestCase
{
	private $doctrineService; 
    private $em; 
    private $databaseEm; 

    public function setUp()
    {
        $this->doctrineService = $this->getContainer()->get('sl_core.doctrine'); 
        $this->em = $this->getContainer()->get('doctrine')->getManager();
        $this->databaseEm = $this->getContainer()->get('doctrine')->getManager('database');
            
        $classes = array(
            'SL\CoreBundle\DataFixtures\ORM\LoadFieldTypeData',
            'SL\CoreBundle\DataFixtures\ORM\Test\LoadDoctrineServiceTestData',
        );
        $this->loadFixtures($classes);
    }

    protected function tearDown()
    {
        unset($this->doctrineService, $this->em, $this->databaseEm);
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
        /*$entityClass1 = $this->em->getRepository('SLCoreBundle:EntityClass\EntityClass')
                                 ->findOneByDisplayName('testEntityDelete_entityClass1');

        $this->assertNull($entityClass1->getDeletedAt()); 
        
        $entityClass1 = $this->em->merge($entityClass1);                          
        $this->doctrineService->entityDelete($entityClass1);

        $filters = $this->em->getFilters();
        $filters->disable('softdeleteable');

        $entityClass1 = $this->em->getRepository('SLCoreBundle:EntityClass\EntityClass')
                                 ->findOneByDisplayName('testEntityDelete_entityClass1');
            
        $filters->enable('softdeleteable');

        $this->assertEquals('testEntityDelete_entityClass1', $entityClass1->getDisplayName()); 
        $this->assertNotNull($entityClass1->getDeletedAt());*/

        /**
          * #2
          * Hard delete
          */
        /*$entityClass2 = $this->em->getRepository('SLCoreBundle:EntityClass\EntityClass')
                                 ->findOneByDisplayName('testEntityDelete_entityClass2');

        $this->assertNull($entityClass2->getDeletedAt()); 
                                 
        $this->doctrineService->entityDelete($entityClass2, true);

        $filters = $this->em->getFilters();
        $filters->disable('softdeleteable');

        $entityClass2 = $this->em->getRepository('SLCoreBundle:EntityClass\EntityClass')
                                 ->findOneByDisplayName('testEntityDelete_entityClass2');
            
        $filters->enable('softdeleteable');

        $this->assertNull($entityClass2);*/
    }

    public function testGetFormatedLogEntries()
    {
        //Generate version 1
        $entityClass1 = $this->getMock('SL\CoreBundle\Entity\EntityClass\EntityClass');
        $entityClass1->expects($this->any())
                    ->method('getId')
                    ->will($this->returnValue(1));

        $testEntityClass1 = new \SL\DataBundle\Entity\TestEntityClass1($entityClass1->getId()); 
        $testEntityClass1->setDisplayName('TestEntityClass1_instance1'); 
        $testEntityClass1->setProperty1("Test1"); 
        $testEntityClass1->setProperty3("Test3"); 
        $testEntityClass1->setProperty4("test@email.com"); 
        $testEntityClass1->setProperty5("45,56"); 
        $testEntityClass1->setProperty6("45,56"); 
        $testEntityClass1->setProperty7("45,56"); 
        $testEntityClass1->setProperty8("www.test.com"); 

        $testTargetEntityClass1 = new \SL\DataBundle\Entity\TestTargetEntityClass1($entityClass1->getId()); 
        $testTargetEntityClass1->setDisplayName('TestTargetEntityClass1_instance1'); 
        $testTargetEntityClass1->setProperty1('Test1'); 
        
        $testEntityClass1->addProperty100($testTargetEntityClass1); 

        $testEntityClass1->setProperty200(array('testVal1','testVal2')); 

        $this->databaseEm->persist($testTargetEntityClass1); 
        $this->databaseEm->persist($testEntityClass1); 
        $this->databaseEm->flush(); 

        //Generate version 2
        $testEntityClass1->setProperty1("Test1 mod1"); 
        $testEntityClass1->setProperty3("Test3 mod1"); 
        $testEntityClass1->setProperty4("testmod1@email.com"); 
        $testEntityClass1->setProperty200(array('testVal1 mod1','testVal2 mod1')); 

        $this->databaseEm->flush(); 

        //Generate version 3
        $testEntityClass1->setProperty1("Test1 mod2"); 
        $testEntityClass1->setProperty3("Test3 mod2"); 
        $testEntityClass1->setProperty4("testmod2@email.com"); 
        $testEntityClass1->setProperty200(array('testVal1 mod2','testVal2 mod2')); 

        $this->databaseEm->flush(); 

        $formatedLogEntries = $this->doctrineService->getFormatedLogEntries($testEntityClass1); 

        $this->assertCount(3, $formatedLogEntries);

        $this->assertEquals(3, $formatedLogEntries[0]['version']);
        $this->assertEquals("Test1 mod2", $formatedLogEntries[0]['data']->getProperty1());
        $this->assertEquals("Test3 mod2", $formatedLogEntries[0]['data']->getProperty3());
        $this->assertEquals("testmod2@email.com", $formatedLogEntries[0]['data']->getProperty4());
        $this->assertEquals("www.test.com", $formatedLogEntries[0]['data']->getProperty8());
        $this->assertEquals(array('testVal1 mod2','testVal2 mod2'), $formatedLogEntries[0]['data']->getProperty200());

        $this->assertEquals(2, $formatedLogEntries[1]['version']);
        $this->assertEquals("Test1 mod1", $formatedLogEntries[1]['data']->getProperty1());
        $this->assertEquals("Test3 mod1", $formatedLogEntries[1]['data']->getProperty3());
        $this->assertEquals("testmod1@email.com", $formatedLogEntries[1]['data']->getProperty4());
        $this->assertEquals("www.test.com", $formatedLogEntries[1]['data']->getProperty8());
        $this->assertEquals(array('testVal1 mod1','testVal2 mod1'), $formatedLogEntries[1]['data']->getProperty200());

        $this->assertEquals(1, $formatedLogEntries[2]['version']);
        $this->assertEquals("Test1", $formatedLogEntries[2]['data']->getProperty1());
        $this->assertEquals("Test3", $formatedLogEntries[2]['data']->getProperty3());
        $this->assertEquals("test@email.com", $formatedLogEntries[2]['data']->getProperty4());
        $this->assertEquals("www.test.com", $formatedLogEntries[2]['data']->getProperty8());
        $this->assertEquals(array('testVal1','testVal2'), $formatedLogEntries[2]['data']->getProperty200());
    } 
}