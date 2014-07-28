<?php

namespace SL\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AbstractEntity
 *
 * @ORM\MappedSuperclass
 *
 */
abstract class AbstractEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="guid", type="string", length=255, nullable=true)
     */
    private $guid;

    /**
     * @var string
     *
     * @ORM\Column(name="technical_name", type="string", length=255, nullable=true)
     */
    private $technicalName;

    /**
     * @var string
     *
     * @ORM\Column(name="display_name", type="string", length=255)
     */
    private $displayName;

    /**
     * @var integer
     *
     * @ORM\Column(name="display_order", type="integer", nullable=true)
     */
    private $displayOrder;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_enabled", type="boolean")
     */
    private $isEnabled = true;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get guid
     *
     * @return integer 
     */
    public function getGuid()
    {
        return $this->guid;
    }

     /**
     * Get guid
     *
     * @param string $guid
     * @return AbstractEntity
     */
    public function setGuid($guid)
    {
        $this->guid = $guid;

        return $this;
    }

    /**
     * Set technicalName
     *
     * @return AbstractEntity
     */
    public function setTechnicalName()
    {
        $this->technicalName = $this->getClassShortName().$this->getId();
        
        return $this;
    }

    /**
     * Get technicalName
     *
     * @return string 
     */
    public function getTechnicalName()
    {
        return $this->technicalName;
    }

    /**
     * Set displayName
     *
     * @param string $displayName
     * @return AbstractEntity
     */
    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;

        return $this;
    }

    /**
     * Get displayName
     *
     * @return string 
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * Set displayOrder
     *
     * @param integer $displayOrder
     * @return AbstractEntity
     */
    public function setDisplayOrder($displayOrder)
    {
        $this->displayOrder = $displayOrder;

        return $this;
    }

    /**
     * Get displayOrder
     *
     * @return integer 
     */
    public function getDisplayOrder()
    {
        return $this->displayOrder;
    }

    /**
     * Set isEnabled
     *
     * @param boolean $isEnabled
     * @return AbstractEntity
     */
    public function setEnabled($isEnabled)
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }

    /**
     * Get isEnabled
     *
     * @return boolean 
     */
    public function isEnabled()
    {
        return $this->isEnabled;
    }


    /**
     * Get short name of current class
     *
     * @return String $classShortName Short name of current class
     */
    public function getClassShortName() 
    {
        $classShortName = ucfirst(basename(strtr(get_class($this), "\\", "/")));
        return $classShortName;
    }
}
