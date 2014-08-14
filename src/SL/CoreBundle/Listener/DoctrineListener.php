<?php 

namespace SL\CoreBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;

use SL\MasterBundle\Entity\AbstractEntity as MasterAbstractEntity;
use SL\CoreBundle\Entity\MappedSuperclass\AbstractEntity as CoreAbstractEntity; 
use SL\DataBundle\Entity\AbstractEntity as DataAbstractEntity;

class DoctrineListener
{   
     /**
     * Function executed before entity persist and flush
     *
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof MasterAbstractEntity) {
            $entity->setGuid(uniqid());
        }
    }

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
    }
}