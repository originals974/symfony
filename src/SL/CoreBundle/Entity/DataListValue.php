<?php

namespace SL\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * DataListValue
 *
 * @ORM\Table(name="data_list_value",uniqueConstraints={
 *      @ORM\UniqueConstraint(name="unique_index_data_list_value_data_list_id_technical_name", columns={"dataList_id", "technical_name"})
 * })
 * @ORM\Entity(repositoryClass="SL\CoreBundle\Entity\DataListValueRepository")
 * @UniqueEntity(fields={"dataList","displayName"})
 */
class DataListValue extends AbstractEntity
{
     /**
     * @var string
     *
     * @ORM\Column(name="icon", type="string", length=255)
     */
    private $icon = 'fa-minus';

    /**
     * @Gedmo\SortableGroup
     * @ORM\ManyToOne(targetEntity="SL\CoreBundle\Entity\DataList", inversedBy="dataListValues")
     * @ORM\JoinColumn(nullable=false)
     */
    private $dataList;

    /**
     * Set icon
     *
     * @param string $icon
     * @return DataListValue
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Get icon
     *
     * @return string 
     */
    public function getIcon()
    {
        return $this->icon;
    }

     /**
     * Set dataList
     *
     * @param \SL\CoreBundle\Entity\DataList $dataList
     * @return DataList
     */
    public function setDataList(\SL\CoreBundle\Entity\DataList $dataList = null)
    {
        $this->dataList = $dataList;

        return $this;
    }

    /**
     * Get dataList
     *
     * @return \SL\CoreBundle\Entity\DataList 
     */
    public function getDataList()
    {
        return $this->dataList;
    }
}
