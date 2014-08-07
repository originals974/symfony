<?php

namespace SL\CoreBundle\Services;

//Symfony classes
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Routing\Router;
use Symfony\Component\Translation\Translator;
use Doctrine\ORM\EntityManager;

//Custom classes
use SL\CoreBundle\Entity\EntityClass;
use SL\DataBundle\Entity\LogEntry;
use SL\CoreBundle\Form\FrontType;
use SL\CoreBundle\Services\DoctrineService;
use SL\CoreBundle\Services\EntityClassService;

/**
 * Front Service
 *
 */
class FrontService
{
    private $formFactory;
    private $router;
    private $em;
    private $entityClassService;
    private $doctrineService;
    private $translator;

    /**
     * Constructor
     *
     * @param FormFactory $formFactory
     * @param Router $router
     * @param EntityManager $em
     * @param EntityClassService $entityClassService
     * @param DoctrineService $doctrineService
     * @param Translator $translator
     *
     */
    public function __construct(FormFactory $formFactory, Router $router, EntityManager $em, EntityClassService $entityClassService, DoctrineService $doctrineService, Translator $translator)
    {
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->em = $em;
        $this->entityClassService = $entityClassService;
        $this->doctrineService = $doctrineService;
        $this->translator = $translator;
    }

    /**
    * Creates entity form
    *
    * @param EntityClass $entityClass EntityClass type of new entity
    * @param Mixed $entity
    *
    * @return Form $form
    */
    public function createCreateForm(EntityClass $entityClass, $entity)
    {
        $entityClass = $this->doctrineService->getDataEntityClass($entityClass->getTechnicalName());

        $form = $this->formFactory->create(new FrontType($this->em, $entityClass, $this->entityClassService, $this->translator), $entity, array(
            'action' => $this->router->generate('front_create', array(
                'id' => $entityClass->getId(),
                )
            ),
            'method' => 'POST',
            'attr' => array(
                'mode' => 'add',  
                'valid-target' => '',  
                'no-valid-target' => 'ajax-modal',
                ),
            'submit_label' => 'create',
            'submit_color' => 'primary',
            'entityClass' => $entityClass,
            )
        );

        return $form;
    }

    /**
    * Update entity form
    *
    * @param EntityClass $entityClass EntityClass type of update entity
    * @param Mixed $entity
    *
    * @return Form $form
    */
    public function createEditForm(EntityClass $entityClass, $entity)
    {
        $entityClass = $this->doctrineService->getDataEntityClass($entityClass->getTechnicalName());

        $form = $this->formFactory->create(new FrontType($this->em, $entityClass, $this->entityClassService, $this->translator), $entity, array(
            'action' => $this->router->generate('front_update', array(
                'id' => $entityClass->getId(),
                'entity_id' => $entity->getId(),
                )
            ),
            'method' => 'PUT',
            'attr' => array(
                'mode' => 'update',  
                'valid-target' => '',  
                'no-valid-target' => 'ajax-modal',
                ),
            'submit_label' => 'update',
            'submit_color' => 'primary',
            'entityClass' => $entityClass,
            )
        );
        
        return $form;
    }


    /**
     * Delete entity form
     *
     * @param EntityClass $entityClass  EntityClass type of remove entity
     * @param Mixed $entity
     *
     * @return Form $form Delete form
     */
    public function createDeleteForm(EntityClass $entityClass, $entity)
    {
        $entityClass = $this->doctrineService->getDataEntityClass($entityClass->getTechnicalName());

        $form = $this->formFactory->create(new FrontType($this->em, $entityClass, $this->entityClassService, $this->translator), $entity, array(
            'action' => $this->router->generate('front_delete', array(
                'id' => $entityClass->getId(),
                'entity_id' => $entity->getId(),
                )
            ),
            'method' => 'DELETE',
            'attr' => array(
                'mode' => 'delete',  
                'valid-target' => '',  
                'no-valid-target' => 'ajax-modal',
                ),
            'submit_label' => 'delete',
            'submit_color' => 'danger',
            'entityClass' => $entityClass,
            )
        );

        return $form;
    }

    /**
    * Update entity version form
    *
    * @param EntityClass $entityClass 
    * @param Mixed $entity
    * @param integer $limit
    *
    * @return Form $form
    */
    public function createEditVersionForm(EntityClass $entityClass, $entity, LogEntry $logEntry, $limit = 5)
    {   
        $form = $this->formFactory->create('sl_core_entity_version', $logEntry, array(
            'action' => $this->router->generate('front_update_version', array(
                'id' => $entityClass->getId(),
                'entity_id' => $entity->getId(),
                )
            ),
            'entity' => $entity,
            'limit' => $limit,
            )
        );

        return $form;
    }

}
