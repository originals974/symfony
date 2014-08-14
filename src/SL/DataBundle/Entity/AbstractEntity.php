<?php

namespace SL\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use SL\MasterBundle\Entity\AbstractEntity as MasterAbstractEntity; 

/**
 * AbstractEntity
 *
 * @ORM\MappedSuperclass()
 * @Gedmo\Loggable(logEntryClass="SL\DataBundle\Entity\LogEntry")
 *
 */
abstract class AbstractEntity extends MasterAbstractEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="entity_class_id", type="integer")
     */
    private $entityClassId;

    /**
     * Constructor : 
     *
     * @param 
     *
     * @return void 
     */
    public function __construct($entityClassId)
    {
        $this->setEntityClassId($entityClassId);
    }

    /**
     * Get entityClassId
     *
     * @return integer 
     */
    public function getEntityClassId()
    {
        return $this->entityClassId;
    }

    /**
     * Get entityClassId
     *
     * @param integer $entityClassId
     * @return AbstractEntity
     */
    public function setEntityClassId($entityClassId)
    {
        $this->entityClassId = $entityClassId;

        return $this;
    }
}
