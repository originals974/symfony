<?php

namespace SL\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ListProperty
 *
 * @ORM\Table(name="list_property")
 * @ORM\Entity(repositoryClass="SL\CoreBundle\Entity\PropertyRepository")
 */
class ListProperty extends Property
{
    /**
     * @ORM\ManyToOne(targetEntity="SL\CoreBundle\Entity\DataList")
     * @Assert\NotBlank()
     */
    private $dataList;


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
