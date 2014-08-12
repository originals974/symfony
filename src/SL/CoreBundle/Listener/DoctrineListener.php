<?php 

namespace SL\CoreBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;

use SL\CoreBundle\Entity\MappedSuperclass\AbstractEntity as CoreAbstractEntity; 
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
        $databaseEm = $args->getEntityManager('database');

        if ($entity instanceof CoreAbstractEntity) {
        	$entity->setTechnicalName(); 
            $entity->setGuid(uniqid());
            $em->flush();
        }

        if($entity instanceof DataAbstractEntity) {
            $entity->setGuid(uniqid());
            $databaseEm->flush();
        }
    }
}