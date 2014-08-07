<?php

namespace SL\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use SL\CoreBundle\Entity\MappedSuperclass\AbstractEntity;

/**
 * FieldCategory
 *
 * @ORM\Table(name="field_category")
 * @ORM\Entity
 * @UniqueEntity(fields="displayName")
 */
class FieldCategory extends AbstractEntity
{
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="SL\CoreBundle\Entity\FieldType", mappedBy="fieldCategory")
     */
    private $fieldTypes;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->fieldTypes = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add fieldType
     *
     * @param \SL\CoreBundle\Entity\FieldType $fieldType
     * @return FieldType
     */
    public function addFieldType(\SL\CoreBundle\Entity\FieldType $fieldType)
    {
        $this->fieldTypes[] = $fieldType;

        return $this;
    }

    /**
     * Remove fieldType
     *
     * @param \SL\CoreBundle\Entity\FieldType $fieldType
     */
    public function removeFieldType(\SL\CoreBundle\Entity\FieldType $fieldType)
    {
        $this->fieldTypes->removeElement($fieldType);
    }

    /**
     * Get fieldTypes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFieldTypes()
    {
        return $this->fieldTypes;
    }
}
