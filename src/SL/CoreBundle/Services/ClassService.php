<?php

namespace SL\CoreBundle\Services;

/**
 * Class Service
 *
 */
class ClassService
{

   /**
     * Get short name of a class
     *
     * @param Mixed $object Object to analyze
     *
     * @return String $classShortName Short name of the class
     */
    public function getClassShortName($object) 
    {
        $classShortName = ucfirst(basename(strtr(get_class($object), "\\", "/")));
        return $classShortName;
    }
}
