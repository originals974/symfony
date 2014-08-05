<?php

namespace SL\CoreBundle\Doctrine;

//Symfony classes
use Doctrine\ORM\Tools\EntityGenerator; 
use Doctrine\ORM\Mapping\ClassMetadataInfo;

//Custom classes

/**
 * SLCoreEntityGenerator
 *
 */
class SLCoreEntityGenerator extends EntityGenerator
{
	/**
     * @param array             $fieldMapping
     * @param ClassMetadataInfo $metadata
     *
     * @return string
     */
    protected function generateFieldMappingPropertyDocBlock(array $fieldMapping, ClassMetadataInfo $metadata)
    {
        $lines = parent::generateFieldMappingPropertyDocBlock($fieldMapping, $metadata);

        if (isset($fieldMapping['versioned']) && $fieldMapping['versioned']) {

            $lines = explode("\n", $lines); 
            $lastLine = array_pop ($lines);
            $lines[] = $this->spaces . ' * @Gedmo\Mapping\Annotation\Versioned';
            $lines[] = $lastLine;  
            $lines = implode("\n", $lines);
        }

        return $lines;
    }
}