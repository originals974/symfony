<?php

namespace SL\DataBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Gedmo\Mapping\Annotation as Gedmo;

use SL\MasterBundle\Entity\AbstractEntity;

/**
 * Document
 *
 * @ORM\Table(name="sl_core_document")
 * @ORM\Entity
 * @Gedmo\Loggable(logEntryClass="SL\DataBundle\Entity\LogEntry")
 * @Gedmo\Uploadable(pathMethod="getUploadRootDir", callback="postMove", filenameGenerator="SHA1", allowOverwrite=true, appendNumber=true)
 */
class Document extends AbstractEntity
{
    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Gedmo\UploadableFilePath
     */
    public $path;

    /**
     * @ORM\Column(name="name", type="string")
     * @Gedmo\UploadableFileName
     */
    private $name;

    /**
     * @ORM\Column(name="mime_type", type="string")
     * @Gedmo\UploadableFileMimeType
     */
    private $mimeType;

    /**
     * @ORM\Column(name="size", type="decimal")
     * @Gedmo\UploadableFileSize
     */
    private $size;

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

    /**
     * Execute after file moved to directory
     *
     * @return string
     */
    public function postMove()
    {
        if (null !== $this->file) {
            $this->setDisplayName($this->file->getClientOriginalName());
        }
    }

    /**
     * Get full path of file
     *
     * @return string
     */
    public function getAbsolutePath()
    {
        return null === $this->path ? null : $this->getUploadRootDir().'/'.$this->path;
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
        return 'uploads/documents';
    }
}