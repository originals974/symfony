<?php 

namespace SL\CoreBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;

use SL\MasterBundle\Entity\AbstractEntity as MasterAbstractEntity;
use SL\CoreBundle\Entity\MappedSuperclass\AbstractEntity as CoreAbstractEntity; 

class DoctrineListener
{   
     /**
     * Function executed before entity persist
     *
     * @param LifecycleEventArgs $args
     *
     * @return void
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
     *
     * @return void
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