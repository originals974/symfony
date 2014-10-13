<?php

namespace SL\CoreBundle\Entity\MappedSuperclass;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

use SL\CoreBundle\Entity\MappedSuperclass\AbstractEntity; 

/**
 * AbstractEntity
 *
 * @ORM\MappedSuperclass
 *
 */
abstract class ParamAbstractEntity extends AbstractEntity
{
    /**
     * @var string
     *
     * @ORM\Column(name="technical_name", type="string", length=255, nullable=true)
     */
    private $technicalName;

    /**
     * @Gedmo\SortablePosition
     * @ORM\Column(name="position", type="integer")
     */
    private $position;

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
