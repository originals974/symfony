<?php

namespace SL\CoreBundle\Entity\Field;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Gedmo\Mapping\Annotation as Gedmo;

use SL\CoreBundle\Entity\MappedSuperclass\AbstractEntity;

/**
 * FieldType
 *
 * @ORM\Table(name="sl_core_field_type")
 * @ORM\Entity
 * @UniqueEntity(fields="displayName")
 */
class FieldType extends AbstractEntity
{
    /**
     * @var string
     *
     * @ORM\Column(name="data_type", type="string", length=255)
     */
    private $dataType;

    /**
     * @var string
     *
     * @ORM\Column(name="form_type", type="string", length=255)
     */
    private $formType;

    /**
     * @var integer
     *
     * @ORM\Column(name="length", type="integer", nullable=true)
     */
    private $length;

    /**
     * @Gedmo\SortableGroup
     * @ORM\ManyToOne(targetEntity="FieldCategory", inversedBy="fieldTypes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $fieldCategory;


    /**
     * Set dataType
     *
     * @param string $dataType
     *
     * @return FieldType
     */
    public function setDataType($dataType)
    {
        $this->dataType = $dataType;

        return $this;
    }

    /**
     * Get dataType
     *
     * @return string 
     */
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * Set formType
     *
     * @param string $formType
     *
     * @return FieldType
     */
    public function setFormType($formType)
    {
        $this->formType = $formType;

        return $this;
    }

    /**
     * Get formType
     *
     * @return string 
     */
    public function getFormType()
    {
        return $this->formType;
    }

    /**
     * Set length
     *
     * @param integer $length
     *
     * @return FieldType
     */
    public function setLength($length)
    {
        $this->length = $length;

        return $this;
    }

    /**
     * Get length
     *
     * @return integer 
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * Set fieldCategory
     *
     * @param FieldCategory $fieldCategory
     *
     * @return FieldType
     */
    public function setFieldCategory(FieldCategory $fieldCategory)
    {
        $this->fieldCategory = $fieldCategory;

        return $this;
    }

    /**
     * Get fieldCategory
     *
     * @return FieldCategory 
     */
    public function getFieldCategory()
    {
        return $this->fieldCategory;
    }
}
