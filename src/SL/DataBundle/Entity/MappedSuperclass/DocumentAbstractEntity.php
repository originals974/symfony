<?php

namespace SL\DataBundle\Entity\MappedSuperclass;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo; 

/**
 * DocumentAbstractEntity
 *
 * @ORM\MappedSuperclass()
 *
 */
abstract class DocumentAbstractEntity extends AbstractEntity
{
    /**
     * @var integer
     *
     * @ORM\OneToOne(targetEntity="SL\DataBundle\Entity\Document", cascade={"persist","remove"})
     */
    private $document;

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
     * @return DocumentAbstractEntity
     */
    public function setDocument(Document $document)
    {
        $this->document = $document;

        return $this;
    }
}
