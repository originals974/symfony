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
     * @param Boolean $isDocument True if new Object is a document
     *
     * @return Array $newNode Object node
     */
    public function createNewObjectNode(Object $object, $isDocument)
    {
        $newNode = array(
            'id' => $object->getTechnicalName(),
            'text' => $object->getDisplayName(),
            'icon' => 'fa '.$object->getIcon(),
            'a_attr' => array(
                'href' => $this->router->generate('object_show', array('id' => $object->getId())),
            ),
        );

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
}
