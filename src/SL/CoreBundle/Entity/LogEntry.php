<?php

namespace SL\CoreBundle\Entity;

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Index;
use Gedmo\Loggable\Entity\MappedSuperclass\AbstractLogEntry;

/**
 * SL\CoreBundle\Entity\LogEntry
 *
 * @Table(
 *     name="data_ext_log_entries",
 *  indexes={
 *      @index(name="log_class_lookup_idx", columns={"object_class"}),
 *      @index(name="log_date_lookup_idx", columns={"logged_at"}),
 *      @index(name="log_user_lookup_idx", columns={"username"}),
 *      @index(name="log_version_lookup_idx", columns={"object_id", "object_class", "version"})
 *  }
 * )
 * @Entity(repositoryClass="SL\CoreBundle\Entity\Repository\LogEntryRepository")
 */
class LogEntry extends AbstractLogEntry
{
    /**
     * All required columns are mapped through inherited superclass
     */
}
