<?php

namespace SL\CoreBundle\Services;

//Symfony classes
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Routing\Router;
use Symfony\Component\Translation\Translator;
use Doctrine\ORM\EntityManager;

//Custom classes
use SL\CoreBundle\Entity\Object;
use SL\CoreBundle\Form\FrontType;
use SL\CoreBundle\Services\DoctrineService;
use SL\CoreBundle\Services\ObjectService;

/**
 * Front Service
 *
 */
class FrontService
{
    private $formFactory;
    private $router;
    private $em;
    private $objectService;
    private $doctrineService;
    private $translator;

    /**
     * Constructor
     *
     * @param FormFactory $formFactory
     * @param Router $router
     * @param EntityManager $em
     * @param ObjectService $objectService
     * @param DoctrineService $doctrineService
     * @param Translator $translator
     *
     */
    public function __construct(FormFactory $formFactory, Router $router, EntityManager $em, ObjectService $objectService, DoctrineService $doctrineService, Translator $translator)
    {
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->em = $em;
        $this->objectService = $objectService;
        $this->doctrineService = $doctrineService;
        $this->translator = $translator;
    }

    /**
    * Creates entity form
    *
    * @param Object $object Object type of new entity
    * @param Mixed $entity
    *
    * @return Form $form
    */
    public function createCreateForm(Object $object, $entity)
    {
        $entityClass = $this->doctrineService->getDataEntityClass($object->getTechnicalName());

        $form = $this->formFactory->create(new FrontType($this->em, $entityClass, $this->objectService, $this->translator), $entity, array(
            'action' => $this->router->generate('front_create', array(
                'id' => $object->getId(),
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
            'object' => $object,
            )
        );

        return $form;
    }

    /**
    * Update entity form
    *
    * @param Object $object Object type of update entity
    * @param Mixed $entity
    *
    * @return Form $form
    */
    public function createEditForm(Object $object, $entity)
    {
        $entityClass = $this->doctrineService->getDataEntityClass($object->getTechnicalName());

        $form = $this->formFactory->create(new FrontType($this->em, $entityClass, $this->objectService, $this->translator), $entity, array(
            'action' => $this->router->generate('front_update', array(
                'id' => $object->getId(),
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
            'object' => $object,
            )
        );
        
        return $form;
    }


    /**
     * Delete entity form
     *
     * @param Object $object  Object type of remove entity
     * @param Mixed $entity
     *
     * @return Form $form Delete form
     */
    public function createDeleteForm(Object $object, $entity)
    {
        $entityClass = $this->doctrineService->getDataEntityClass($object->getTechnicalName());

        $form = $this->formFactory->create(new FrontType($this->em, $entityClass, $this->objectService, $this->translator), $entity, array(
            'action' => $this->router->generate('front_delete', array(
                'id' => $object->getId(),
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
            'object' => $object,
            )
        );

        return $form;
    }
}
