<?php

namespace SL\CoreBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;

use SL\CoreBundle\Entity\EntityClass\EntityClass;
use SL\CoreBundle\Entity\EntityClass\Property;
use SL\CoreBundle\Entity\EntityClass\PropertyEntity;
use SL\CoreBundle\Entity\EntityClass\PropertyChoice;
use SL\CoreBundle\Entity\Choice\ChoiceList;
use SL\CoreBundle\Entity\Choice\ChoiceItem;
use SL\MasterBundle\Entity\AbstractEntity; 

/**
 * Test Service
 *
 */
class TestService
{
    public function getPropertyByDisplayName(EntityClass $entityClass, $displayName){

        foreach($entityClass->getProperties() as $property){

            if($property->getDisplayName() === $displayName) {
                return $property; 
            }
        }
    }

    public function populateEntity(EntityClass $entityClass, AbstractEntity &$entity, $data){

        $entity->setDisplayName('entity'); 

        foreach($entityClass->getProperties() as $property){

            if(array_key_exists($property->getDisplayName(), $data)) {
                if($property->isMultiple() && $property->getFieldType()->getFormType() == 'entity'){
                    foreach($data[$property->getDisplayName()] as $value){
                        $entity->{"add".$property->getTechnicalName()}($value);
                    }
                }
                else{
                    $entity->{"set".$property->getTechnicalName()}($data[$property->getDisplayName()]);
                }
            }
        }
    }

    public function getSimpleEntityClassInstances($nbOfEntityClass = 2){

        $entityClasses = array(); 

        for($i=1; $i<=$nbOfEntityClass; $i++){
            $entityClasses[] = $this->getSimpleEntityClassInstance($i);
        }

        return $entityClasses; 
    }

    public function getSimpleEntityClassWithParentsInstance($index = 1, $nbParents = 1){

        $entityClass = $this->getSimpleEntityClassInstance($index);

        $currentEntityClass = $entityClass; 
        $entityClasses = array($entityClass); 
        for($i=1;$i<=$nbParents;$i++) {
            
            $parentEntityClass = $this->getSimpleEntityClassInstance($index + $i); 
            $currentEntityClass->setParent($parentEntityClass);
            $currentEntityClass = $parentEntityClass; 

            $entityClasses[] = $parentEntityClass; 
        }

        return $entityClasses; 
    }

    public function getSimpleEntityClassInstance($index = 1){

        $entityClass = new EntityClass(); 
        $entityClass->setDisplayName('entity_class_'.$index); 

        return $entityClass; 
    }

    public function getEntityClassInstance(ObjectManager $manager) {

        $entityClass = new EntityClass(); 
        $entityClass->setDisplayName('entity_class'); 

        $propertiesData = array(
            array(
                'name' => 'property_text',
                'type' => 'text',
                ),
            array(
                'name' => 'property_textarea',
                'type' => 'textarea',
                ),
            array(
                'name' => 'property_email',
                'type' => 'email',
                ),
            array(
                'name' => 'property_money',
                'type' => 'money',
                ),
            array(
                'name' => 'property_number',
                'type' => 'number',
                ),
            array(
                'name' => 'property_percent',
                'type' => 'percent',
                ),
            array(
                'name' => 'property_url',
                'type' => 'url',
                ),
            array(
                'name' => 'property_choice',
                'type' => 'choice',
                ),
            array(
                'name' => 'property_choice_multiple',
                'type' => 'choice',
                'multiple' => true,
                ),
            array(
                'name' => 'property_entity',
                'type' => 'entity',
                'target_name' => 'target_entity_class_1'
                ),
            array(
                'name' => 'property_entity_multiple',
                'type' => 'entity',
                'target_name' => 'target_entity_class_2',
                'multiple' => true,
                ),
            array(
                'name' => 'property_genemu_jquerydate',
                'type' => 'genemu_jquerydate',
                ),
            ); 

        foreach($propertiesData as $propertyData){
            $fieldType = $manager->getRepository('SLCoreBundle:Field\FieldType')->findOneByFormType($propertyData['type']);
            
            switch($propertyData['type']){
                case 'choice' : 
                    $property = new PropertyChoice($fieldType); 
                    $property->setChoiceList($this->getChoiceListInstance());

                    break; 
                case 'entity' :
                    $property = new PropertyEntity($fieldType);
                    $property->setTargetEntityClass($this->getTargetEntityInstance($propertyData['target_name'])); 
                    break; 
                default:
                    $property = new Property($fieldType); 
                    break;
            }

            if(array_key_exists('multiple', $propertyData)){
                $property->setIsMultiple($propertyData['multiple']);
            }

            $property->setDisplayName($propertyData['name']);

            $saveProperty = clone $property; 
            $entityClass->addProperty($saveProperty); 
            $saveProperty->setEntityClass($entityClass); 
        }
        
        return $entityClass; 
    }

    public function getChoiceListInstances($nbOfChoiceList = 2, $nbOfItems = 0){

        $choiceLists = array(); 

        for($i=1; $i<=$nbOfChoiceList; $i++){
            $choiceLists[] = $this->getChoiceListInstance($i, $nbOfItems);
        }

        return $choiceLists;    
    }

    public function getChoiceListInstance($index = 1, $nbOfItems = 0){

        $choiceList = new ChoiceList(); 
        $choiceList->setDisplayName('choice_list_'.$index);

        for($i=1; $i<=$nbOfItems; $i++){
            $item = new ChoiceItem($choiceList); 
            $item->setDisplayName('Item_'.$i); 
        }

        return $choiceList;    
    }

    public function getTargetEntityInstance($targetName){

        $targetEntityClass = new EntityClass(); 
        $targetEntityClass->setDisplayName($targetName); 

        return $targetEntityClass;  
    }

}
