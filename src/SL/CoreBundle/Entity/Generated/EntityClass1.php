<?php

namespace SL\CoreBundle\Entity\Generated;

use Doctrine\ORM\Mapping as ORM;

/**
 * EntityClass1
 *
 * @ORM\Table()
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string", length=0)
 * @ORM\DiscriminatorMap({})
 * @ORM\Entity(repositoryClass="SL\CoreBundle\Entity\Repository\SharedEntityRepository")
 */
class EntityClass1 extends \SL\CoreBundle\Entity\MappedSuperclass\DataAbstractEntity
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="Property5", type="date", nullable=true)
     * @Gedmo\Mapping\Annotation\Versioned
     */
    private $Property5;


    /**
     * Set Property5
     *
     * @param \DateTime $property5
     * @return EntityClass1
     */
    public function setProperty5($property5)
    {
        $this->Property5 = $property5;

        return $this;
    }

    /**
     * Get Property5
     *
     * @return \DateTime 
     */
    public function getProperty5()
    {
        return $this->Property5;
    }
}
