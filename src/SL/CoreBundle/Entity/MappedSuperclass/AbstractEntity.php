<?php

namespace SL\CoreBundle\Entity\MappedSuperclass;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * AbstractEntity
 *
 * @ORM\MappedSuperclass
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 *
 */
abstract class AbstractEntity
{
    /**
     * Hook timestampable behavior
     * updates createdAt, updatedAt fields
     */
    use TimestampableEntity;
    
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
     * @Gedmo\SortablePosition
     * @ORM\Column(name="position", type="integer")
     */
    private $position;

    /**
     * @ORM\Column(name="deletedAt", type="datetime", nullable=true)
     */
    private $deletedAt;

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
     * @return string 
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
     * Set position
     *
     * @param integer $position
     * @return AbstractEntity
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return integer 
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set deletedAt
     *
     * @param DateTime $deletedAt
     * @return AbstractEntity
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return integer 
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
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
