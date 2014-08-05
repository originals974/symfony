<?php

namespace SL\CoreBundle\Services;

//Custom classes
use SL\CoreBundle\Entity\Object;
use SL\CoreBundle\Entity\Property;
use SL\CoreBundle\Entity\DataList;
use SL\CoreBundle\Entity\DataListValue;

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
     * Get Object icon
     *
     * @param Object $object Object
     *
     * @return String icon
     */
    public function getObjectIcon(Object $object)
    {
        return 'fa '.$object->getIcon(); 
    }

    /**
     * Get root Object icon
     *
     * @param String $option Option of icon
     *
     * @return String icon
     */
    public function getRootObjectIcon($option=null)
    {
        return 'fa fa-archive '.$option; 
    }

    /**
     * Get root Sub Object icon
     *
     * @param String $option Option of icon
     *
     * @return String icon
     */
    public function getRootSubObjectIcon($option=null)
    {
        return 'fa fa-sitemap '.$option; 
    }

    /**
     * Get root DataList icon
     *
     * @param String $option Option of icon
     *
     * @return String icon
     */
    public function getRootDataListIcon($option=null)
    {
        return 'fa fa-list '.$option; 
    }

    /**
     * Get DataList icon
     *
     * @param DataList $dataList DataList
     *
     * @return String icon
     */
    public function getDataListIcon(DataList $dataList)
    { 
        return 'fa fa-list'; 
    }

    /**
     * Get DataListValue icon
     *
     * @param DataListValue $dataListValue DataListValue
     *
     * @return String icon
     */
    public function getDataListValueIcon(DataListValue $dataListValue)
    {
        return 'fa '.$dataListValue->getIcon(); 
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
     * @param Property $property Property
     *
     * @return String icon
     */
    public function getPropertyIcon(Property $property=null)
    {
        if($property->getFieldType()->getFormType() == 'entity' ){
            $icon = $this->getRootObjectIcon();
        }
        else if($property->getFieldType()->getFormType() == 'choice' ){
            $icon = $this->getRootDataListIcon();
        }
        else{
            $icon = $this->getDefaultPropertyIcon();
        }

        return $icon; 
    }
}
