<?php

namespace SL\CoreBundle\Services\EntityClass;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Routing\Router;
use Symfony\Component\Form\Form;   

use SL\CoreBundle\Entity\EntityClass\EntityClass;

/**
 * EntityClass Service
 *
 */
class EntityClassService
{
    private $em;
    private $translator;
    private $formFactory;
    private $router;

    /**
     * Constructor
     *
     * @param RegistryInterface $registry
     * @param Translator $translator
     * @param FormFactory $formFactory
     * @param Router $router
     *
     */
    public function __construct(RegistryInterface $registry, Translator $translator, FormFactory $formFactory, Router $router)
    {
        $this->em = $registry->getManager();
        $this->translator = $translator;
        $this->formFactory = $formFactory;
        $this->router = $router;
    }

    /**
    * Create create form for $entityClass
    *
    * @param EntityClass $entityClass
    *
    * @return Form $form
    */
    public function createCreateForm(EntityClass $entityClass)
    {
        $parentEntityClass = $entityClass->getParent();

        $form = $this->formFactory->create('sl_core_entity_class', $entityClass, array(
            'action' => $this->router->generate('entity_class_create', array(
                'entity_class_id' =>  ( $parentEntityClass !== null)?$parentEntityClass->getId():0,
                )
            ),
            'method' => 'POST',
            'attr' => array(
                'valid-target' => '', 
                'no-valid-target' => 'ajax-modal',
                'mode' => 'add',  
                ),
            'submit_label' => 'create',
            'submit_color' => 'primary',
            )
        );

        return $form;
    }

    /**
    * Create update form for $entityClass
    *
    * @param EntityClass $entityClass
    *
    * @return Form $form
    */
    public function createEditForm(EntityClass $entityClass)
    {    
        $form = $this->formFactory->create('sl_core_entity_class', $entityClass, array(
            'action' => $this->router->generate('entity_class_update', array('entity_class_id' => $entityClass->getId())),
            'method' => 'PUT',
            'attr' => array(
                'valid-target' => '', 
                'no-valid-target' => 'ajax-modal',
                'mode' => 'update', 
                ),
            'submit_label' => 'update',
            'submit_color' => 'primary',
            )
        );

        return $form;
    }

    /**
     * Create delete form for $entityClass
     *
     * @param EntityClass $entityClass
     *
     * @return Form $form
     */
    public function createDeleteForm(EntityClass $entityClass)
    {   
        $form = $this->formFactory->create('sl_core_entity_class', $entityClass, array(
            'action' => $this->router->generate('entity_class_delete', array('entity_class_id' => $entityClass->getId())),
            'method' => 'DELETE',
            'attr' => array(
                'valid-target' => '', 
                'no-valid-target' => 'ajax-modal',
                'mode' => 'delete',  
                ),
            'submit_label' => 'delete',
            'submit_color' => 'danger',
            )
        );

        return $form;
    }

    /**
     * Create update form for calculated name of $entityClass
     *
     * @param EntityClass $entityClass
     *
     * @return Form $form
     */
    public function createEditCalculatedNameForm(EntityClass $entityClass)
    {      
        $form = $this->formFactory->create('sl_core_entity_class_calculated_name', $entityClass, array(
            'action' => $this->router->generate('entity_class_update_calculated_name', array(
                'entity_class_id' => $entityClass->getId(),
                )),
            )
        );

        return $form;
    }


   /**
     * Verify if $entityClass could be delete
     *
     * @param EntityClass $entityClass
     *
     * @return array $integrityError Title and error message
     */
    public function integrityControlBeforeDelete(EntityClass $entityClass) 
    {
        $integrityError = null;

        //Check if entity class is associated to another entity class
        $targetEntityClass = $this->em->getRepository('SLCoreBundle:EntityClass\PropertyEntity')->findByTargetEntityClass($entityClass);

        if($targetEntityClass != null){
            $title = $this->translator->trans('delete.error.title');
            $message = $this->translator->trans('delete.entity_class.reference.error.message');

            $integrityError = array(
                'title' => $title,
                'message' => $message,
                );

            return $integrityError;
        }

        return $integrityError; 
    }

     /**
     * Init calculated name for $entityClass
     *
     * @param EntityClass $entityClass
     *
     * @return void
     */
    public function initCalculatedName(EntityClass $entityClass){

        if($entityClass->getParent() != null){
            $entityClass->setCalculatedName($entityClass->getParent()->getCalculatedName());
        }
        else{
            $defaultProperty = $entityClass->getProperties()->first(); 
            $entityClass->setCalculatedName('%'.$defaultProperty->getTechnicalName().'%');
        }
        
        $this->em->flush(); 
    }

    /**
     * Get $entityClass and all of its parents
     *
     * @param EntityClass $entityClass
     *
     * @return array $parents
     */
    public function getPath(EntityClass $entityClass){

        $parents = array($entityClass); 
        $this->getParent($entityClass, $parents);

        return $this->orderedEntityClassesProperties($parents); 
    }

    /**
     * Get direct parent of $entityClass and add it to $parents
     *
     * @param EntityClass $entityClass
     * @param array $parents
     *
     * @return void
     */
    private function getParent(EntityClass $entityClass, array &$parents){

        if($entityClass->getParent() != null){
            array_unshift($parents, $entityClass->getParent()); 
            $this->getParent($entityClass->getParent(), $parents);
        }
    }

    /**
     * Get $path of $entityClass  
     *
     * @param EntityClass $entityClass
     *
     * @return string $path Ex : EntityClass1->EntityClass2->EntityClass3->...
     */
    public function getEntityClassPath(EntityClass $entityClass){

        $entityClasses = $this->getPath($entityClass); 

        $path = "";
        foreach($entityClasses as $key=>$entityClass){
            if($key == 0) {
                $path = $entityClass->getDisplayName();
            }
            else {
                $path = $path." -> ".$entityClass->getDisplayName(); 
            }
        }

        return $path; 
    }


    /**
     * Ordered properties for $entityClasses
     *
     * @param array $entityClasses Array of entity class
     *
     * @return array $orderedEntityClasss
     */
    private function orderedEntityClassesProperties(array $entityClasses){

        $filters = $this->em->getFilters();
        $filters->disable('softdeleteable');

        $orderedEntityClasses = array(); 

        foreach($entityClasses as $entityClass){
            $entityClass = $this->em->getRepository('SLCoreBundle:EntityClass\EntityClass')->fullFindById($entityClass->getId());
            $orderedEntityClasses[] = $entityClass;
        }

        $filters->enable('softdeleteable');

        return $orderedEntityClasses; 
    }
}
