<?php

namespace SL\CoreBundle\Services;

//Symfony classes
use Symfony\Component\Routing\Router; 
use Symfony\Component\Translation\Translator;

//Custom classes
use SL\CoreBundle\Entity\Object;
use SL\CoreBundle\Entity\Property;
use SL\CoreBundle\Entity\DataList;
use SL\CoreBundle\Entity\DataListValue;
use SL\CoreBundle\Services\IconService;

/**
 * JSTree Service
 *
 */
class JSTreeService
{
    private $router; 
    private $translator;
    private $icon;

    /**
     * Constructor
     *
     * @param Router $router
     * @param Translator $translator
     * @param IconService $icon
     *
     */
    public function __construct(Router $router, Translator $translator, IconService $icon)
    {
        $this->router = $router;
        $this->translator = $translator;
        $this->icon = $icon; 
    }

    /**
     * Create new Object node
     *
     * @param Object $object New Object 
     * @param Property $property Default Property
     * @param Object $parentObject Parent Object of new Object
     * @param Boolean $isDocument True if new Object is a document
     *
     * @return Array $newNode Object node
     */
    public function createNewObjectNode(Object $object, Property $property=null, Object $parentObject=null, $isDocument)
    {
        $newNode = array(
            'id' => $object->getTechnicalName(),
            'text' => $object->getDisplayName(),
            'icon' => 'fa '.$object->getIcon(),
            'a_attr' => array(
                'href' => $this->router->generate('object_show', array('id' => $object->getId())),
            ),
            'children' => array(), 
        );

        if($parentObject == null) {

            if($isDocument) {
                $text = 'sub_document'; 
            }
            else {
                $text = 'sub_object'; 
            }

            $subObjectNode = array(
                    'text' => $this->translator->trans(/** @Ignore */$text),
                    'icon' => $this->icon->getRootSubObjectIcon(),
                );
            array_push($newNode['children'],$subObjectNode); 
        
            $defaultPropertyNode = array(
                'id' => $property->getTechnicalName(),
                'text' => $property->getDisplayName(),
                'icon' => $this->icon->getDefaultPropertyIcon(),
            );
            array_push($newNode['children'],$defaultPropertyNode); 
        }

        return $newNode; 
    }

    /**
     * Create new Property node
     *
     * @param Property $property New Property
     *
     * @return Array $newNode Property node
     */
    public function createNewPropertyNode(Property $property)
    {
        $newNode = array(
            'id' => $property->getTechnicalName(),
            'text' => $property->getDisplayName(),
            'icon' => $this->icon->getPropertyIcon($property),
        ); 

        return $newNode; 
    }

    /**
     * Create new DataList node
     *
     * @param DataList $dataList New DataList
     *
     * @return Array $newNode DataList node
     */
    public function createNewDataListNode(DataList $dataList)
    {
        $newNode = array(
            'id' => $dataList->getTechnicalName(),
            'text' => $dataList->getDisplayName(),
            'icon' => $this->icon->getDataListIcon($dataList),
            'a_attr' => array(
                'href' => $this->router->generate('data_list_show', array('id' => $dataList->getId())),
            ),
        );

        return $newNode; 
    }

    /**
     * Create new DataListValue node
     *
     * @param DataListValue $dataListValue New DataListValue
     *
     * @return Array $newNode DataListValue node
     */
    public function createNewDataListValueNode(DataListValue $dataListValue)
    {
        $newNode = array(
            'id' => $dataListValue->getTechnicalName(),
            'text' => $dataListValue->getDisplayName(),
            'icon' => 'fa '.$dataListValue->getIcon(),
        ); 

        return $newNode; 
    }

    /**
     * Update Object node
     *
     * @param Object $object Updated Object 
     *
     * @return Array $updatedNode Object node
     */
    public function updateObjectNode(Object $object) 
    {
        $updatedNode = array(
            'id' => $object->getTechnicalName(),
            'text' => $object->getDisplayName(),
        ); 

        return $updatedNode;
    }

    /**
     * Update Property node
     *
     * @param Property $property Updated Property 
     *
     * @return Array $updatedNode Property node
     */
    public function updatePropertyNode(Property $property) 
    {
        $updatedNode = array(
            'id' => $property->getTechnicalName(),
            'text' => $property->getDisplayName(),
        ); 

        return $updatedNode;
    }

    /**
     * Update DataList node
     *
     * @param DataList $dataList Updated DataList 
     *
     * @return Array $updatedNode DataList node
     */
    public function updateDataListNode(DataList $dataList) 
    {
        $updatedNode = array(
            'id' => $dataList->getTechnicalName(),
            'text' => $dataList->getDisplayName(),
        ); 

        return $updatedNode;
    }

    /**
     * Update DataListValue node
     *
     * @param DataListValue $dataListValue Updated DataListValue 
     *
     * @return Array $updatedNode DataListValue node
     */
    public function updateDataListValueNode(DataListValue $dataListValue) 
    {
        $updatedNode = array(
            'id' => $dataListValue->getTechnicalName(),
            'text' => $dataListValue->getDisplayName(),
        ); 

        return $updatedNode;
    }

}
