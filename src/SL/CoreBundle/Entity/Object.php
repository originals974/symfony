<?php

namespace SL\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use SL\CoreBundle\Validator\Constraints as SLCoreAssert;

/**
 * Object
 *
 * @ORM\Table(name="object",uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unique_index_object_technical_name", columns={"technical_name"})}))
 * @ORM\Entity(repositoryClass="SL\CoreBundle\Entity\ObjectRepository")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(fields="displayName")
 */
class Object extends AbstractEntity
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
     * @ORM\OneToMany(targetEntity="SL\CoreBundle\Entity\Property", mappedBy="object", cascade={"persist","remove"})
     */
    private $properties;

    /**
     * @ORM\ManyToOne(targetEntity="SL\CoreBundle\Entity\Object", inversedBy="childrenObject")
     */
    private $parentObject;

    /**
     * @ORM\OneToMany(targetEntity="SL\CoreBundle\Entity\Object", mappedBy="parentObject")
     */
    private $childrenObject;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_parent", type="boolean")
     */
    private $isParent;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_document", type="boolean")
     */
    private $isDocument;

    /**
     * Constructor
     */
    public function __construct($parentObject, $isDocument, $defaultPropertyfieldType)
    {
        $this->properties = new \Doctrine\Common\Collections\ArrayCollection();
        $this->childrenObject = new \Doctrine\Common\Collections\ArrayCollection();
        $this->setParentObject($parentObject);
        $this->setDocument($isDocument);

        if($defaultPropertyfieldType != null) {
            $property = new Property(); 
            $property->setDisplayName('Nom');
            $property->setDisplayOrder(1);
            $property->setRequired(true);
            $property->setFieldType($defaultPropertyfieldType);

            $property->setObject($this);
            $this->addProperty($property);
        }
    }

    /**
     * Set calculatedName
     *
     * @param string $calculatedName
     * @return Object
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
     * @return Object
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
     * @return Object
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
     * Add child Object
     *
     * @param \SL\CoreBundle\Entity\Object $child
     * @return Object
     */
    public function addChildrenObject(\SL\CoreBundle\Entity\Object $child)
    {
        $this->childrenObject[] = $child;

        return $this;
    }

    /**
     * Remove child Object
     *
     * @param \SL\CoreBundle\Entity\Object $child
     */
    public function removeChildrenObject(\SL\CoreBundle\Entity\Object $child)
    {
        $this->child->removeElement($child);
    }

    /**
     * Get children Object Collection
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getChildrenObject()
    {
        return $this->childrenObject;
    }

    /**
     * Set parent Object
     *
     * @param string $parent
     * @return Object
     */
    public function setParentObject($parentObject)
    {
        if($parentObject != null) {
            $this->setParent(false);
        }
        else{
            $this->setParent(true);
        }
        
        $this->parentObject = $parentObject;

        return $this;
    }

    /**
     * Get parent
     *
     * @return Object 
     */
    public function getParentObject()
    {
        return $this->parentObject;
    }

    /**
     * Set isParent
     *
     * @param boolean $isParent
     * @return Object
     */
    public function setParent($isParent)
    {
        $this->isParent = $isParent;

        return $this;
    }

    /**
     * Get isParent
     *
     * @return boolean 
     */
    public function isParent()
    {
        return $this->isParent;
    }

    /**
     * Set isDocument
     *
     * @param boolean $isDocument
     * @return Object
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
    * @ORM\PostPersist
    */
    public function initObject()
    {
        if(!$this->isParent()){
            $this->setCalculatedName($this->getParentObject()->getCalculatedName());
        }   
    }
}
