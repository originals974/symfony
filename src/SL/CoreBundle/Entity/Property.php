<?php

namespace SL\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Gedmo\Mapping\Annotation as Gedmo;

use SL\CoreBundle\Entity\MappedSuperclass\AbstractEntity;

/**
 * Property
 *
 * @ORM\InheritanceType("JOINED")
 * @ORM\Table(name="property",uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unique_index_property_entity_class_id_technical_name", columns={"entityClass_id", "technical_name"})
 * })
 * @ORM\Entity(repositoryClass="SL\CoreBundle\Entity\Repository\PropertyRepository")
 * @ORM\HasLifecycleCallbacks()
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
     * @ORM\ManyToOne(targetEntity="SL\CoreBundle\Entity\EntityClass", inversedBy="properties")
     * @ORM\JoinColumn(nullable=false)
     */
    private $entityClass;

    /**
     * @ORM\ManyToOne(targetEntity="SL\CoreBundle\Entity\FieldType")
     * @ORM\JoinColumn(nullable=false)
     */
    private $fieldType;

    /**
     * Set isRequired
     *
     * @param boolean $isRequired
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
     * @return Property
     */
    public function setMultiple($isMultiple)
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
     * @param \SL\CoreBundle\Entity\EntityClass $entityClass
     * @return EntityClass
     */
    public function setEntityClass(\SL\CoreBundle\Entity\EntityClass $entityClass = null)
    {
        $this->entityClass = $entityClass;

        return $this;
    }

    /**
     * Get entityClass
     *
     * @return \SL\CoreBundle\Entity\EntityClass 
     */
    public function getEntityClass()
    {
        return $this->entityClass;
    }

    /**
     * Set fieldType
     *
     * @param \SL\CoreBundle\Entity\FieldType $fieldType
     * @return FieldType
     */
    public function setFieldType(\SL\CoreBundle\Entity\FieldType $fieldType = null)
    {
        $this->fieldType = $fieldType;

        return $this;
    }

    /**
     * Get fieldType
     *
     * @return \SL\CoreBundle\Entity\FieldType 
     */
    public function getFieldType()
    {
        return $this->fieldType;
    }
}
