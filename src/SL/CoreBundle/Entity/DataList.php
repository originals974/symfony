<?php

namespace SL\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * DataList
 *
 * @ORM\Table(name="data_list",uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unique_index_data_list_technical_name", columns={"technical_name"}),
 *     @ORM\UniqueConstraint(name="unique_index_data_list_display_name", columns={"display_name"})
 * })
 * @ORM\Entity(repositoryClass="SL\CoreBundle\Entity\DataListRepository")
 * @UniqueEntity(fields="displayName")
 */
class DataList extends AbstractEntity
{
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="SL\CoreBundle\Entity\DataListValue", mappedBy="dataList", cascade={"remove"})
     */
    private $dataListValues;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->dataListValues = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add dataListValue
     *
     * @param \SL\CoreBundle\Entity\DataListValue $dataListValue
     * @return DataList
     */
    public function addDataListValue(\SL\CoreBundle\Entity\DataListValue $dataListValue)
    {
        $this->dataListValues[] = $dataListValue;

        return $this;
    }

    /**
     * Remove dataListValue
     *
     * @param \SL\CoreBundle\Entity\DataListValue $dataListValue
     */
    public function removeDataListValue(\SL\CoreBundle\Entity\DataListValue $dataListValue)
    {
        $this->dataListValues->removeElement($dataListValue);
    }

    /**
     * Get dataListValues
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDataListValues()
    {
        return $this->dataListValues;
    }
}
