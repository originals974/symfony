<?php

namespace SL\CoreBundle\Entity\MappedSuperclass;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

use SL\CoreBundle\Entity\MappedSuperclass\AbstractEntity; 
use SL\CoreBundle\Entity\EntityClass\EntityClass; 
use SL\CoreBundle\Entity\Document; 

/**
 * AbstractEntity
 *
 * @ORM\MappedSuperclass()
 * @Gedmo\Loggable(logEntryClass="SL\CoreBundle\Entity\LogEntry")
 *
 */
abstract class DataAbstractEntity extends AbstractEntity
{
    /**
     * @var EntityClass
     *
     * @ORM\ManyToOne(targetEntity="SL\CoreBundle\Entity\EntityClass\EntityClass")
     */
    private $entityClass;

    /**
     * @var integer
     *
     * @ORM\OneToOne(targetEntity="SL\CoreBundle\Entity\Document", cascade={"persist","remove"})
     */
    private $document;

    /**
     * Constructor : 
     *
     * @param 
     *
     * @return void 
     */
    public function __construct(EntityClass $entityClass)
    {
        $this->setEntityClass($entityClass);
    }

    /**
     * Get entityClass
     *
     * @return EntityClass 
     */
    public function getEntityClass()
    {
        return $this->entityClass;
    }

    /**
     * Get EntityClass
     *
     * @param EntityClass $entityClass
     * @return AbstractEntity
     */
    public function setEntityClass(EntityClass $entityClass)
    {
        $this->entityClass = $entityClass;

        return $this;
    }

    /**
     * Get document
     *
     * @return Document 
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Set document
     *
     * @param Document $document
     * @return DataAbstractEntity
     */
    public function setDocument(Document $document)
    {
        $this->document = $document;

        return $this;
    }
}
