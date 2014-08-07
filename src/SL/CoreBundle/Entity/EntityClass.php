<?php

namespace SL\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

use SL\CoreBundle\Validator\Constraints as SLCoreAssert;
use SL\CoreBundle\Entity\MappedSuperclass\AbstractEntity;

/**
 * EntityClass
 *
 * @Gedmo\Tree(type="nested")
 * @ORM\Table(name="entity_class",uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unique_index_entity_class_technical_name", columns={"technical_name"})
 *  })
 * @ORM\Entity(repositoryClass="SL\CoreBundle\Entity\Repository\EntityClassRepository")
 * @UniqueEntity(fields="displayName")
 */
class EntityClass extends AbstractEntity
{
    /**
     * @var string
     *
     * @ORM\Column(name="calculated_name", type="string", length=255, nullable=true)
     *
     * @Assert\Length(
     *      max = "255"
     *)
     * @SLCoreAssert\CalculatedNamePattern
     */
    private $calculatedName;

    /**
     * @var string
     *
     * @ORM\Column(name="icon", type="string", length=255)
     */
    private $icon = 'fa-question';

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="SL\CoreBundle\Entity\Property", mappedBy="entityClass", cascade={"persist"})
     */
    private $properties;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     */
    private $lft;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     */
    private $lvl;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     */

    private $rgt;
     /**
     * @Gedmo\TreeRoot
     * @ORM\Column(name="root", type="integer", nullable=true)
     */
    private $root;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="EntityClass", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="EntityClass", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    private $children;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isDocument", type="boolean")
     */
    private $isDocument = false;

    /**
     * Constructor
     * @param FieldType $fieldType Fieldtype of default property
     * @param EntityClass $parentEntityClass Parent entityClass of new entityClass
     */
    public function __construct(FieldType  $fieldType = null, EntityClass $parent = null)
    {
        $this->properties = new \Doctrine\Common\Collections\ArrayCollection();

        //Create default property "name"
        if($fieldType != null) {
            $defaultProperty = new Property();
            $defaultProperty->setFieldType($fieldType);
            $defaultProperty->setDisplayName('Nom');
            $defaultProperty->setRequired(true);
            $defaultProperty->setEntityClass($this);
            $this->addProperty($defaultProperty);
        }
        
        //Associate created entityClass with its parent
        if($parent != null) {
            $this->setParent($parent);
        }
    }

    /**
     * Set calculatedName
     *
     * @param string $calculatedName
     * @return EntityClass
     */
    public function setCalculatedName($calculatedName)
    {
        $this->calculatedName = $calculatedName;

        return $this;
    }

    /**
     * Get calculatedName
     *
     * @return string 
     */
    public function getCalculatedName()
    {
        return $this->calculatedName;
    }

    /**
     * Set icon
     *
     * @param string $icon
     * @return EntityClass
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
     * Add property
     *
     * @param \SL\CoreBundle\Entity\Property $property
     * @return EntityClass
     */
    public function addProperty(\SL\CoreBundle\Entity\Property $property)
    {
        $this->properties[] = $property;

        return $this;
    }

    /**
     * Remove property
     *
     * @param \SL\CoreBundle\Entity\Property $property
     */
    public function removeProperty(\SL\CoreBundle\Entity\Property $property)
    {
        $this->properties->removeElement($property);
    }

    /**
     * Get properties
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * Set EntityClass parent 
     *
     * @param EntityClass $parent
     * @return EntityClass
     */
    public function setParent(EntityClass $parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * Get parent
     *
     * @return EntityClass 
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set isDocument
     *
     * @param boolean $isDocument
     * @return EntityClass
     */
    public function setDocument($isDocument)
    {
        $this->isDocument = $isDocument;

        return $this;
    }

    /**
     * Get isDocument
     *
     * @return boolean 
     */
    public function isDocument()
    {
        return $this->isDocument;
    }

    /**
     * Get root
     *
     * @return boolean 
     */
    public function isRoot()
    {
        return $this->root;
    }
}