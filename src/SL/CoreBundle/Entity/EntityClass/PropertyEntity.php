<?php

namespace SL\CoreBundle\Entity\EntityClass;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * PropertyEntity
 *
 * @ORM\Table(name="sl_core_property_entity")
 * @ORM\Entity(repositoryClass="SL\CoreBundle\Entity\Repository\PropertyRepository")
 */
class PropertyEntity extends Property
{
    /**
     * @ORM\ManyToOne(targetEntity="EntityClass", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank()
     */
    private $targetEntityClass;


    /**
     * Set targetEntityClass
     *
     * @param EntityClass $targetEntityClass
     *
     * @return EntityClass 
     */
    public function setTargetEntityClass(EntityClass $targetEntityClass)
    {
        $this->targetEntityClass = $targetEntityClass;

        return $this;
    }

    /**
     * Get targetEntityClass
     *
     * @return EntityClass 
     */
    public function getTargetEntityClass()
    {
        return $this->targetEntityClass;
    }
}
