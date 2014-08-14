<?php

namespace SL\CoreBundle\Entity\Field;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;

use SL\CoreBundle\Entity\MappedSuperclass\AbstractEntity;

/**
 * FieldCategory
 *
 * @ORM\Table(name="sl_core_field_category")
 * @ORM\Entity
 * @UniqueEntity(fields="displayName")
 */
class FieldCategory extends AbstractEntity
{
    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="FieldType", mappedBy="fieldCategory")
     */
    private $fieldTypes;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->fieldTypes = new ArrayCollection();
    }

    /**
     * Add fieldType
     *
     * @param FieldType $fieldType
     *
     * @return FieldType
     */
    public function addFieldType(FieldType $fieldType)
    {
        $this->fieldTypes[] = $fieldType;

        return $this;
    }

    /**
     * Remove fieldType
     *
     * @param FieldType $fieldType
     */
    public function removeFieldType(FieldType $fieldType)
    {
        $this->fieldTypes->removeElement($fieldType);
    }

    /**
     * Get fieldTypes
     *
     * @return ArrayCollection 
     */
    public function getFieldTypes()
    {
        return $this->fieldTypes;
    }
}
