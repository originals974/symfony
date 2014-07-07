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
     * Get root Document icon
     *
     * @param String $option Option of icon
     *
     * @return String icon
     */
    public function getRootDocumentIcon($option=null)
    {
        return 'fa fa-file '.$option; 
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
        $option = (!$object->isEnabled())?'text-danger':null; 
        return 'fa '.$object->getIcon().' '.$option; 
    }

    /**
     * Get only Object icon name
     *
     * @param Object $object Object
     *
     * @return String Only icon name
     */
    /*public function getPacthObjectIcon(Object $object)
    {
        $option = (!$object->isEnabled())?'text-danger':null; 
        return str_replace("icon-", "", $this->getObjectIcon($object)); 
    }*/

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
        $option = (!$dataList->isEnabled())?'text-danger':null; 
        return 'fa fa-list '.$option; 
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
        $option = (!$dataListValue->isEnabled())?'text-danger':null; 
        return 'fa '.$dataListValue->getIcon().' '.$option; 
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
        $option = (!$property->isEnabled())?'text-danger':null; 

        if($property->getFieldType()->getTechnicalName() == 'entity' ){
            $icon = $this->getRootObjectIcon($option);
        }
        else if($property->getFieldType()->getTechnicalName() == 'data_list' ){
            $icon = $this->getRootDataListIcon($option);
        }
        else{
            $icon = $this->getDefaultPropertyIcon($option);
        }

        return $icon; 
    }
}
