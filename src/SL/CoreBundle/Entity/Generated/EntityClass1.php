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

}
