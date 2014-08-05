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
     * Create new object node
     *
     * @param Object $object New object 
     *
     * @return Array $newNode Object node
     */
    public function createNewObjectNode(Object $object)
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
     * Create new dataList node
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
     * Shorten text property of a node if it's over max length
     *
     * @param String $textToShorten
     * @param String $maxLength
     *
     * @return Array $newNode DataList node
     */
    public function shortenTextNode($textToShorten, $maxLength){

        if(strlen($textToShorten) > $maxLength) {

            $halfLength = $maxLength/2; 
            $shortedText = substr($textToShorten, 0, $halfLength) . "....." . substr($textToShorten, -$halfLength);
        }
        else{
            $shortedText = $textToShorten;
        }

        return $shortedText;
    }
}
