<?php

namespace SL\CoreBundle\Entity\Field;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;

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
     * @var Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="SL\CoreBundle\Entity\Field\FieldType", mappedBy="fieldCategory")
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
     * @param SL\CoreBundle\Entity\Field\FieldType $fieldType
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
     * @param SL\CoreBundle\Entity\Field\FieldType $fieldType
     */
    public function removeFieldType(FieldType $fieldType)
    {
        $this->fieldTypes->removeElement($fieldType);
    }

    /**
     * Get fieldTypes
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getFieldTypes()
    {
        return $this->fieldTypes;
    }
}
