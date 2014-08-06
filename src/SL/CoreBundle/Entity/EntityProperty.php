<?php

namespace SL\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * EntityProperty
 *
 * @ORM\Table(name="entity_property")
 * @ORM\Entity(repositoryClass="SL\CoreBundle\Entity\Repository\PropertyRepository")
 */
class EntityProperty extends Property
{
    /**
     * @ORM\ManyToOne(targetEntity="SL\CoreBundle\Entity\Object")
     * @Assert\NotBlank()
     */
    private $targetObject;


    /**
     * Set targetObject
     *
     * @param \SL\CoreBundle\Entity\Object $targetObject
     * @return EntityProperty
     */
    public function setTargetObject(\SL\CoreBundle\Entity\Object $targetObject = null)
    {
        $this->targetObject = $targetObject;

        return $this;
    }

    /**
     * Get targetObject
     *
     * @return \SL\CoreBundle\Entity\Object 
     */
    public function getTargetObject()
    {
        return $this->targetObject;
    }
}
