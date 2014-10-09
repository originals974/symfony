<?php

namespace SL\CoreBundle\Search\Transformer;
 
use Doctrine\ORM\EntityManager; 
use FOS\ElasticaBundle\Transformer\ModelToElasticaTransformerInterface;
use Elastica\Document;

use SL\CoreBundle\Search\Transformer\TransformerTools; 

class ObjectToElasticaTransformer implements ModelToElasticaTransformerInterface
{
	//private $em;

    /*public function __construct($em)
    {
   		$this->em = $em;
    }*/

    /*public function entityToFiedlsMapping(AbstractEntity $entity){

        $entityClass = $this->em->getRepository('SLCoreBundle:EntityClass\EntityClass')->find($entity->getEntityClassId());

        $mapping = array(); 

        $this->entityClassToFieldsMapping($mapping, $entityClass); 

        return $mapping; 
    }

    private function entityClassToFieldsMapping(array &$mapping, EntityClass $entityClass){

        $defaultMapping = array(
            'guid' => null,
            'displayName' => null,
            'entityClassId' => null,
            ); 

        foreach($entityClass->getProperties() as $property){
            if($property->getFormType == 'entity'){
                $entityClass = $property->getTargetEntityClass();

                $mapping[$property->getTechnicalName()] = $defaultMapping; 
                $this->entityClassToFieldsMapping($mapping[$property->getTechnicalName()], $entityClass);
            }
            else{
                $mapping[$property->getTechnicalName()] = null; 
            }
        }
    }*/

    /**
     * Transforms an object into an elastica object having the required keys
     *
     * @param object $object the object to convert
     * @param array  $fields the keys we want to have in the returned array
     * @param boolean  $overwriteMapping Overwirte mapping for object
     *
     * @return Document
     **/
    public function transform($object, array $fields, $overwriteMapping = true)
    {
    	if($overwriteMapping) {

    		$transformerTools = new TransformerTools(); 
    		$fieldsMapping = $transformerTools->entityToFiedlsMapping($object);

    		var_dump($fieldsMapping); 

    		$fields = array(
	    		'guid' => null,
	    		'displayName' => null,
	    		'entityClassId' => null,
	    		'Property1' => null,
	    		'PropertyEntity4' => array(
	    			'type' => 'object',
	    			'properties' => array(
	    				'guid' => null,
	    				'displayName' => null,
	    				'entityClassId' => null,
	    				'Property2' => null,
	    				'PropertyEntity5' => array(
	    					'type' => 'object',
	    					'properties' => array(
	    						'guid' => null,
	    						'displayName' => null,
	    						'entityClassId' => null,
	    						'Property3' => null,
	    						),
	    					),
	    				),
	    			),
	    		);
    	}

        $identifier = $object->getId();
        $document = new Document($identifier);

        foreach ($fields as $key => $mapping) {
            if ($key == '_parent') {
                $property = (null !== $mapping['property'])?$mapping['property']:$mapping['type'];
                $value = $object->{'get'.$property}();
                $document->setParent($value->getId());
                continue;
            }

            $value = $object->{'get'.$key}();

            if (isset($mapping['type']) && in_array($mapping['type'], array('nested', 'object')) && isset($mapping['properties']) && !empty($mapping['properties'])) {
                /* $value is a nested document or object. Transform $value into
                 * an array of documents, respective the mapped properties.
                 */
                $document->set($key, $this->transformNested($value, $mapping['properties']));
                continue;
            }

            if (isset($mapping['type']) && $mapping['type'] == 'attachment') {
                if ($value instanceof \SplFileInfo) {
                    $document->addFile($key, $value->getPathName());
                } else {
                    $document->addFileContent($key, $value);
                }
                continue;
            }

            $document->set($key, $this->normalizeValue($value));
        }
 
        return $document;
    }

    /**
     * transform a nested document or an object property into an array of ElasticaDocument
     *
     * @param array|\Traversable|\ArrayAccess $objects the object to convert
     * @param array $fields the keys we want to have in the returned array
     *
     * @return array
     */
    protected function transformNested($objects, array $fields)
    {
        if (is_array($objects) || $objects instanceof \Traversable || $objects instanceof \ArrayAccess) {
            $documents = array();
            foreach ($objects as $object) {
                $document = $this->transform($object, $fields, false);
                $documents[] = $document->getData();
            }

            return $documents;
        } elseif (null !== $objects) {
            $document = $this->transform($objects, $fields, false);

            return $document->getData();
        }

        return array();
    }

    /**
     * Attempts to convert any type to a string or an array of strings
     *
     * @param mixed $value
     *
     * @return string|array
     */
    protected function normalizeValue($value)
    {
        $normalizeValue = function(&$v)
        {
            if ($v instanceof \DateTime) {
                $v = $v->format('c');
            } elseif (!is_scalar($v) && !is_null($v)) {
                $v = (string)$v;
            }
        };

        if (is_array($value) || $value instanceof \Traversable || $value instanceof \ArrayAccess) {
            $value = is_array($value) ? $value : iterator_to_array($value, false);
            array_walk_recursive($value, $normalizeValue);
        } else {
            $normalizeValue($value);
        }

        return $value;
    }
}
