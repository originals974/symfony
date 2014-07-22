<?php

namespace SL\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * FieldCategory
 *
 * @ORM\Table(name="field_category",uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unique_index_field_category_technical_name", columns={"technical_name"}),
 *     @ORM\UniqueConstraint(name="unique_index_field_category_display_name", columns={"display_name"})
 * })
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
     * @return Object
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
