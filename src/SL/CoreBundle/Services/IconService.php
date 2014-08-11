<?php

namespace SL\CoreBundle\Services;

//Custom classes
use SL\CoreBundle\Entity\EntityClass\EntityClass;
use SL\CoreBundle\Entity\EntityClass\Property;
use SL\CoreBundle\Entity\Choice\ChoiceList;
use SL\CoreBundle\Entity\Choice\ChoiceItem;

/**
 * Icon Service
 *
 */
class IconService
{
    /**
     * Get root Server icon
     *
     * @param String $option Option of icon
     *
     * @return String icon
     */
    public function getRootServerIcon($option=null)
    {
        return 'fa fa-database '.$option; 
    }

    /**
     * Get EntityClass icon
     *
     * @param EntityClass\EntityClass $entityClass EntityClass
     *
     * @return String icon
     */
    public function getEntityClassIcon(EntityClass $entityClass)
    {
        return 'fa '.$entityClass->getIcon(); 
    }

    /**
     * Get root EntityClass icon
     *
     * @param String $option Option of icon
     *
     * @return String icon
     */
    public function getRootEntityClassIcon($option=null)
    {
        return 'fa fa-archive '.$option; 
    }

    /**
     * Get root Sub EntityClass icon
     *
     * @param String $option Option of icon
     *
     * @return String icon
     */
    public function getRootSubEntityClassIcon($option=null)
    {
        return 'fa fa-sitemap '.$option; 
    }

    /**
     * Get root choice list icon
     *
     * @param String $option Option of icon
     *
     * @return String icon
     */
    public function getRootChoiceListIcon($option=null)
    {
        return 'fa fa-list '.$option; 
    }

    /**
     * Get choice list icon
     *
     * @param ChoiceList $choiceList
     *
     * @return String icon
     */
    public function getChoiceListIcon(ChoiceList $choiceList)
    { 
        return 'fa fa-list'; 
    }

    /**
     * Get choice item icon
     *
     * @param ChoiceItem $choiceItem
     *
     * @return String icon
     */
    public function getChoiceItemIcon(ChoiceItem $choiceItem)
    {
        return 'fa '.$choiceItem->getIcon(); 
    }

    /**
     * Get default Property icon
     *
     * @param String $option Option of icon
     *
     * @return String icon
     */
    public function getDefaultPropertyIcon($option=null)
    {
        return 'fa fa-circle '.$option; 
    }

    /**
     * Get Property icon
     *
     * @param EntityClass\Property $property Property
     *
     * @return String icon
     */
    public function getPropertyIcon(Property $property=null)
    {
        if($property->getFieldType()->getFormType() == 'entity' ){
            $icon = $this->getRootEntityClassIcon();
        }
        else if($property->getFieldType()->getFormType() == 'choice' ){
            $icon = $this->getRootChoiceListIcon();
        }
        else{
            $icon = $this->getDefaultPropertyIcon();
        }

        return $icon; 
    }
}
