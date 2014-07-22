<?php 

namespace SL\CoreBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use SL\CoreBundle\Entity\AbstractEntity; 

class DoctrineListener
{
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();

        if ($entity instanceof AbstractEntity) {
        	$entity->setTechnicalName(); 
        	
        }
        
        $em->flush(); 
    }
}