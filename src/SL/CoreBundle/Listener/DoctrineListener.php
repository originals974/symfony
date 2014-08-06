<?php 

namespace SL\CoreBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use SL\CoreBundle\Entity\AbstractEntity as CoreAbstractEntity; 
use SL\DataBundle\Entity\AbstractEntity as DataAbstractEntity;

class DoctrineListener
{   
    /**
     * Function executed after entity persist and flush
     *
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();

        if ($entity instanceof CoreAbstractEntity) {
        	$entity->setTechnicalName(); 
            $em->flush();
        }

        if($entity instanceof CoreAbstractEntity || $entity instanceof DataAbstractEntity ) {
            $entity->setGuid(uniqid());
            $em->flush();
        }

         
    }
}