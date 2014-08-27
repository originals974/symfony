<?php

namespace SL\CoreBundle\Tests\Services\EntityClass;

use Liip\FunctionalTestBundle\Test\WebTestCase;

class PropertyServiceTest extends WebTestCase
{
	private $propertyService; 
	private $em; 
	private $translator;

  public function setUp()
  {
    $this->propertyService = $this->getContainer()->get('sl_core.property'); 
    $this->em = $this->getContainer()->get('doctrine.orm.entity_manager'); 
    $this->translator = $this->getContainer()->get('translator'); 

    $classes = array(
        'SL\CoreBundle\DataFixtures\ORM\LoadFieldTypeData',
    );
  }

  protected function tearDown()
	{
	  unset($this->propertyService, $this->em, $this->translator);
	}

  public function testCreateCreateForm()
  {
    $entityClass = $this->getMock('SL\CoreBundle\Entity\EntityClass\EntityClass');
    $entityClass->expects($this->any())
                ->method('getId')
                ->will($this->returnValue(1));

    $property = $this->getMock('SL\CoreBundle\Entity\EntityClass\Property');
    $property->expects($this->any())
             ->method('getEntityClass')
             ->will($this->returnValue($entityClass));

    $form = $this->propertyService->createCreateForm($property);
    
    $this->assertCount(2, $form);

    $this->assertArrayHasKey('selectForm', $form);
    $this->assertArrayHasKey('mainForm', $form);

    $this->assertInstanceOf('Symfony\Component\Form\Form', $form['selectForm']);
    $this->assertInstanceOf('Symfony\Component\Form\Form', $form['mainForm']);
  }

  public function testCreateEditForm()
  {
    $entityClass = $this->getMock('SL\CoreBundle\Entity\EntityClass\EntityClass');
    $entityClass->expects($this->any())
                ->method('getId')
                ->will($this->returnValue(1));

    /**
      * #1
      * For text property
      */
    $fieldTypeText = $this->em->getRepository('SLCoreBundle:Field\FieldType')
                             ->findOneByFormType('text');             

    $property1 = $this->getMock('SL\CoreBundle\Entity\EntityClass\Property');
    $property1->expects($this->any())
             ->method('getId')
             ->will($this->returnValue(1));
    $property1->expects($this->any())
             ->method('getEntityClass')
             ->will($this->returnValue($entityClass));
    $property1->expects($this->any())
             ->method('getFieldType')
             ->will($this->returnValue($fieldTypeText));

    $form = $this->propertyService->createEditForm($property1);
   
    $this->assertInstanceOf('Symfony\Component\Form\Form', $form);

    /**
      * #2
      * For entity property
      */
    $fieldTypeText = $this->em->getRepository('SLCoreBundle:Field\FieldType')
                              ->findOneByFormType('entity');             

    $property2 = $this->getMock('SL\CoreBundle\Entity\EntityClass\PropertyEntity');
    $property2->expects($this->any())
             ->method('getId')
             ->will($this->returnValue(1));
    $property2->expects($this->any())
             ->method('getEntityClass')
             ->will($this->returnValue($entityClass));
    $property2->expects($this->any())
             ->method('getFieldType')
             ->will($this->returnValue($fieldTypeText));

    $form = $this->propertyService->createEditForm($property2);
   
    $this->assertInstanceOf('Symfony\Component\Form\Form', $form);

    /**
      * #3
      * For choice property
      */
    $fieldTypeText = $this->em->getRepository('SLCoreBundle:Field\FieldType')
                              ->findOneByFormType('choice');             

    $property3 = $this->getMock('SL\CoreBundle\Entity\EntityClass\PropertyChoice');
    $property3->expects($this->any())
             ->method('getId')
             ->will($this->returnValue(1));
    $property3->expects($this->any())
             ->method('getEntityClass')
             ->will($this->returnValue($entityClass));
    $property3->expects($this->any())
             ->method('getFieldType')
             ->will($this->returnValue($fieldTypeText));

    $form = $this->propertyService->createEditForm($property3);
   
    $this->assertInstanceOf('Symfony\Component\Form\Form', $form);

  }

  public function testCreateDeleteForm()
  {
    $entityClass = $this->getMock('SL\CoreBundle\Entity\EntityClass\EntityClass');
    $entityClass->expects($this->any())
                ->method('getId')
                ->will($this->returnValue(1));          

    $property = $this->getMock('SL\CoreBundle\Entity\EntityClass\Property');
    $property->expects($this->any())
             ->method('getId')
             ->will($this->returnValue(1));
    $property->expects($this->any())
             ->method('getEntityClass')
             ->will($this->returnValue($entityClass));

    $form = $this->propertyService->createDeleteForm($property);
   
    $this->assertInstanceOf('Symfony\Component\Form\Form', $form);
  }

  public function testIntegrityControlBeforeDelete()
  {
    /**
      * #1
      * Not used in calculated name
      */
    $entityClass1 = $this->getMock('SL\CoreBundle\Entity\EntityClass\EntityClass');
    $entityClass1->expects($this->once())
                 ->method('getCalculatedName')
                 ->will($this->returnValue('%Property1 Property2%'));  

    $property1 = $this->getMock('SL\CoreBundle\Entity\EntityClass\Property');
    $property1->expects($this->any())
             ->method('getTechnicalName')
             ->will($this->returnValue('Property3'));
    $property1->expects($this->any())
             ->method('getEntityClass')
             ->will($this->returnValue($entityClass1));

    $integrityError = $this->propertyService->integrityControlBeforeDelete($property1);
    $this->assertNull($integrityError);

    /**
    * #2
    * Used in calculated name
    */
    $entityClass2 = $this->getMock('SL\CoreBundle\Entity\EntityClass\EntityClass');
    $entityClass2->expects($this->once())
                ->method('getCalculatedName')
                ->will($this->returnValue('%Property1 Property2 Property3%'));  

    $property2 = $this->getMock('SL\CoreBundle\Entity\EntityClass\Property');
    $property2->expects($this->any())
             ->method('getTechnicalName')
             ->will($this->returnValue('Property2'));
    $property2->expects($this->any())
             ->method('getEntityClass')
             ->will($this->returnValue($entityClass2));

    $integrityError = $this->propertyService->integrityControlBeforeDelete($property2);
    $expectedIntegrityError = array(
        'title' => $this->translator->trans('delete.error.title'),
        'message' => $this->translator->trans('property.delete.calculated_name.error.message')
        );

    $this->assertEquals($expectedIntegrityError, $integrityError);
  }
}