<?php 

namespace SL\CoreBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use SL\CoreBundle\Entity\AbstractEntity; 

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

        $entity->setGuid(uniqid());

        if ($entity instanceof AbstractEntity) {
            //Init technical name of new entity
        	$entity->setTechnicalName(); 
        }

        $em->flush(); 
    }
}