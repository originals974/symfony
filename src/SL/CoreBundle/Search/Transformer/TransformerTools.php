<?php

namespace SL\CoreBundle\Search\Transformer;
 
use Doctrine\ORM\EntityManager; 
use Symfony\Component\DependencyInjection\ContainerAware;

class TransformerTools extends ContainerAware
{
    public function entityToFiedlsMapping( $entity){

        var_dump($this->container); 

        $em = $this->get('doctrine.orm.entity_manager'); 

        $entityClass = $em->getRepository('SLCoreBundle:EntityClass\EntityClass')->find($entity->getEntityClassId());

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
    }
}