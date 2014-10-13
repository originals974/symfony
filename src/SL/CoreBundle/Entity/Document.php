<?php

namespace SL\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Gedmo\Mapping\Annotation as Gedmo;
use \SplFileInfo;

use SL\CoreBundle\Entity\MappedSuperclass\AbstractEntity;

/**
 * Document
 *
 * @ORM\Table(name="data_document")
 * @ORM\Entity
 * @Gedmo\Loggable(logEntryClass="SL\CoreBundle\Entity\LogEntry")
 * @ORM\HasLifecycleCallbacks
 */
class Document extends AbstractEntity
{
    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    public $path;

    /**
     * @var UploadedFile
     *
     * @Assert\File(maxSize="6000000")
     */
    public $file;

    /**
     * Get file
     *
     * @return UploadedFile 
     */
    public function getFile() 
    {
        if (null !== $this->file) {
            return $this->file; 
        }
    }

    public function getEncodedFile() 
    {
        return new SplFileInfo($this->getAbsolutePath());
    }


    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        if (null !== $this->file) {
            $this->setDisplayName($this->file->getClientOriginalName()); 
            $this->path = sha1(uniqid(mt_rand(), true)).'.'.$this->file->guessExtension();
        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        if (null === $this->file) {
            return;
        }

        $this->file->move($this->getUploadRootDir(), $this->path);

        unset($this->file);
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        if ($file = $this->getAbsolutePath()) {
            unlink($file);
        }
    }


    /**
     * Get full path of file
     *
     * @return string
     */
    public function getAbsolutePath()
    {
        return null === $this->path ? null : $this->getUploadRootDir().$this->path;
    }

    /**
     * Get relative path of file
     *
     * @return string
     */
    public function getWebPath()
    {
        return null === $this->path ? null : $this->getUploadDir().'/'.$this->path;
    }

    /**
     * Get full directory
     * where file is stored
     *
     * @return string
     */
    protected function getUploadRootDir()
    {
        return __DIR__.'/../../../../web/'.$this->getUploadDir();
    }

    /**
     * Get relative directory
     * where file is stored
     *
     * @return string
     */
    protected function getUploadDir()
    {
        return 'uploads/documents/';
    }
}