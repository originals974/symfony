<?php

namespace SL\CoreBundle\Entity\EntityClass;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Gedmo\Mapping\Annotation as Gedmo;

use SL\CoreBundle\Entity\MappedSuperclass\AbstractEntity;
use SL\CoreBundle\Entity\Field\FieldType; 

/**
 * Property
 *
 * @ORM\InheritanceType("JOINED")
 * @ORM\Table(name="sl_core_property",uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unique_index_property_entity_class_id_technical_name", columns={"entityClass_id", "technical_name"})
 * })
 * @ORM\Entity(repositoryClass="SL\CoreBundle\Entity\Repository\PropertyRepository")
 * @UniqueEntity(fields={"entityClass", "displayName"}, repositoryMethod="findByEntityClassAndDisplayName")
 */
class Property extends AbstractEntity
{
    /**
     * @var boolean
     *
     * @ORM\Column(name="is_required", type="boolean")
     */
    private $isRequired=false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_multiple", type="boolean")
     */
    private $isMultiple=false;

    /**
     * @Gedmo\SortableGroup
     * @ORM\ManyToOne(targetEntity="EntityClass", inversedBy="properties")
     * @ORM\JoinColumn(nullable=false)
     */
    private $entityClass;

    /**
     * @ORM\ManyToOne(targetEntity="SL\CoreBundle\Entity\Field\FieldType")
     * @ORM\JoinColumn(nullable=false)
     */
    private $fieldType;

     /**
     * Constructor : Create an property
     * associated with $fieldType 
     *
     * @param FieldType $fieldType|null 
     *
     * @return void 
     */
    public function __construct(FieldType  $fieldType = null)
    {
        if($fieldType != null){
            $this->setFieldType($fieldType);
        }
    }

    /**
     * Set isRequired
     *
     * @param boolean $isRequired
     *
     * @return Property
     */
    public function setRequired($isRequired)
    {
        $this->isRequired = $isRequired;

        return $this;
    }

    /**
     * Get isRequired
     *
     * @return boolean 
     */
    public function isRequired()
    {
        return $this->isRequired;
    }

    /**
     * Set isMultiple
     *
     * @param boolean $isMultiple
     *
     * @return Property
     */
    public function setIsMultiple($isMultiple)
    {
        $this->isMultiple = $isMultiple;

        return $this;
    }

    /**
     * Get isMultiple
     *
     * @return boolean 
     */
    public function isMultiple()
    {
        return $this->isMultiple;
    }

    /**
     * Set entityClass
     *
     * @param EntityClass $entityClass
     *
     * @return EntityClass
     */
    public function setEntityClass(EntityClass $entityClass)
    {
        $this->entityClass = $entityClass;

        return $this;
    }

    /**
     * Get entityClass
     *
     * @return EntityClass 
     */
    public function getEntityClass()
    {
        return $this->entityClass;
    }

    /**
     * Set fieldType
     *
     * @param FieldType $fieldType
     *
     * @return FieldType
     */
    public function setFieldType(FieldType $fieldType)
    {
        $this->fieldType = $fieldType;

        return $this;
    }

    /**
     * Get fieldType
     *
     * @return FieldType 
     */
    public function getFieldType()
    {
        return $this->fieldType;
    }
}
