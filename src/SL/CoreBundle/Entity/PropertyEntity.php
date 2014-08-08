<?php

namespace SL\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * PropertyEntity
 *
 * @ORM\Table(name="property_entity")
 * @ORM\Entity(repositoryClass="SL\CoreBundle\Entity\Repository\PropertyRepository")
 */
class PropertyEntity extends Property
{
    /**
     * @ORM\ManyToOne(targetEntity="SL\CoreBundle\Entity\EntityClass")
     * @Assert\NotBlank()
     */
    private $targetEntityClass;


    /**
     * Set targetEntityClass
     *
     * @param \SL\CoreBundle\Entity\EntityClass $targetEntityClass
     * @return PropertyEntity
     */
    public function setTargetEntityClass(\SL\CoreBundle\Entity\EntityClass $targetEntityClass = null)
    {
        $this->targetEntityClass = $targetEntityClass;

        return $this;
    }

    /**
     * Get targetEntityClass
     *
     * @return \SL\CoreBundle\Entity\EntityClass 
     */
    public function getTargetEntityClass()
    {
        return $this->targetEntityClass;
    }
}
