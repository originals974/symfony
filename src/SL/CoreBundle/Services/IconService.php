<?php

namespace SL\CoreBundle\Services;

use SL\CoreBundle\Entity\EntityClass\EntityClass;
use SL\CoreBundle\Entity\Choice\ChoiceItem;

/**
 * Icon Service
 *
 */
class IconService
{
    /**
     * Get root server icon name
     *
     * @param string $option
     *
     * @return string $iconName
     */
    public function getRootServerIcon($option=null)
    {
        $iconName = 'fa fa-database '.$option;

        return $iconName; 
    }

    /**
     * Get icon name for $entityClass
     *
     * @param EntityClass $entityClass
     *
     * @return string $iconName
     */
    public function getEntityClassIcon(EntityClass $entityClass)
    {
        $iconName = 'fa '.$entityClass->getIcon();

        return $iconName; 
    }

    /**
     * Get root entity class icon name
     *
     * @param string $option
     *
     * @return string $iconName 
     */
    public function getRootEntityClassIcon($option=null)
    {
        $iconName = 'fa fa-archive '.$option;

        return $iconName; 
    }

    /**
     * Get root choice list icon name
     *
     * @param string $option
     *
     * @return string $iconName 
     */
    public function getRootChoiceListIcon($option=null)
    {
        $iconName = 'fa fa-list '.$option; 

        return $iconName;
    }

    /**
     * Get choice list icon name
     *
     * @param string $option
     *
     * @return string $iconName 
     */
    public function getChoiceListIcon($option=null)
    { 
        $iconName = 'fa fa-list'.$option;

        return $iconName; 
    }

    /**
     * Get choice item icon
     *
     * @param ChoiceItem $choiceItem
     *
     * @return string $iconName 
     */
    public function getChoiceItemIcon(ChoiceItem $choiceItem)
    {
        $iconName = 'fa '.$choiceItem->getIcon(); 

        return $iconName; 
    }
}
