<?php

namespace SL\CoreBundle\Services;

//Symfony classes
use Symfony\Component\Routing\Router; 

//Custom classes
use SL\CoreBundle\Entity\Object;
use SL\CoreBundle\Entity\Choice\ChoiceList;
use SL\CoreBundle\Services\IconService;

/**
 * JSTree Service
 *
 */
class JSTreeService
{
    private $router; 
    private $icon;

    /**
     * Constructor
     *
     * @param Router $router
     * @param IconService $icon
     *
     */
    public function __construct(Router $router, IconService $icon)
    {
        $this->router = $router;
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
     * Create new choice list node
     *
     * @param ChoiceList $choiceList
     *
     * @return Array $newNode choice list node
     */
    public function createNewChoiceListNode(ChoiceList $choiceList)
    {
        $newNode = array(
            'id' => $choiceList->getTechnicalName(),
            'text' => $choiceList->getDisplayName(),
            'icon' => $this->icon->getChoiceListIcon($choiceList),
            'a_attr' => array(
                'href' => $this->router->generate('choice_list_show', array('id' => $choiceList->getId())),
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
     * @return Array $newNode ChoiceList node
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
