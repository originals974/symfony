<?php 

namespace SL\CoreBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;

use SL\CoreBundle\Entity\MappedSuperclass\AbstractEntity;
use SL\CoreBundle\Entity\MappedSuperclass\ParamAbstractEntity; 

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

        if ($entity instanceof AbstractEntity) {
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

        if ($entity instanceof ParamAbstractEntity) {
        	$entity->setTechnicalName(); 
            $em->flush();
        }
    }
}