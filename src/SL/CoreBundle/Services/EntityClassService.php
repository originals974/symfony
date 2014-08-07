<?php

namespace SL\CoreBundle\Services;

//Symfony classes
use Symfony\Component\Translation\Translator;
use Symfony\Component\Form\Form;   
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Routing\Router; 

//Custom classes
use SL\CoreBundle\Entity\EntityClass;

/**
 * EntityClass Service
 *
 */
class EntityClassService
{
    private $em;
    private $databaseEm;
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
        $this->databaseEm = $registry->getManager('database');
        $this->translator = $translator;
        $this->formFactory = $formFactory;
        $this->router = $router;
    }

    /**
    * Create entityClass form
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
                'id' =>  ( $parentEntityClass != null)?$parentEntityClass->getId():0,
                )
            ),
            'method' => 'POST',
            'submit_label' => 'create',
            'submit_color' => 'primary',
            )
        );

        return $form;
    }

    /**
    * Update entityClass form
    *
    * @param EntityClass $entityClass
    *
    * @return Form $form
    */
    public function createEditForm(EntityClass $entityClass)
    {    
        $form = $this->formFactory->create('sl_core_entity_class', $entityClass, array(
            'action' => $this->router->generate('entity_class_update', array('id' => $entityClass->getId())),
            'method' => 'PUT',
            'submit_label' => 'update',
            'submit_color' => 'primary',
            )
        );

        return $form;
    }

    /**
    * Update Calculated name form 
    *
    * @param EntityClass $entityClass
    *
    * @return Form $form
    */
    public function createEditCalculatedNameForm(EntityClass $entityClass)
    {      
        $form = $this->formFactory->create('sl_core_entity_class_calculated_name', $entityClass, array(
            'action' => $this->router->generate('entity_class_update_calculated_name', array(
                'id' => $entityClass->getId(),
                )
            ),
            )
        );

        return $form;
    }

    /**
     * Delete entityClass Form
     *
     * @param EntityClass $entityClass
     *
     * @return Form $form
     */
    public function createDeleteForm(EntityClass $entityClass)
    {   
        $form = $this->formFactory->create('sl_core_entity_class', $entityClass, array(
            'action' => $this->router->generate('entity_class_delete', array('id' => $entityClass->getId())),
            'method' => 'DELETE',
            'submit_label' => 'delete',
            'submit_color' => 'danger',
            )
        );

        return $form;
    }

   /**
     * Verify integrity of an entityClass before delete
     *
     * @param EntityClass $entityClass EntityClass to delete
     *
     * @return Array $integrityError Title and error message
     */
    public function integrityControlBeforeDelete(EntityClass $entityClass) 
    {
        $integrityError = null;

        //Check if entityClass is link to another
        $targetEntityClass = $this->em->getRepository('SLCoreBundle:EntityProperty')->findByTargetEntityClass($entityClass);

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
     * Calculate displayName attribute of a new entity 
     * by using calculatedName attribute of entityClass
     *
     * @param Mixed $entity
     * @param EntityClass $entityClass
     *
     * @return String $displayName DisplayName of new entity
     */
    public function calculateDisplayName($entity, EntityClass $entityClass) 
    { 
        $patternString = $entityClass->getCalculatedName();

        $patternArray = explode("%", $patternString);

        foreach($patternArray as $key => $pattern) {
            
            if(strpos(strtolower($pattern), 'property') !== false){

                $methodName = 'get'.ucfirst($pattern);
                $patternArray[$key] = $entity->$methodName(); 
            }
        }

        $displayName = implode($patternArray);

        return $displayName; 
    }

     /**
     * Init calculated name for a new entityClass
     *
     * @param EntityClass $entityClass EntityClass
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
    * Refresh displayName of entity linked to entityClass
    *
    * @param EntityClass $entityClass 
    *
    */
    public function refreshCalculatedName(EntityClass $entityClass){

        $entities = $this->databaseEm ->getRepository('SLDataBundle:'.$entityClass->getTechnicalName())
                                ->findAll(); 

        foreach($entities as $entity) {

            $displayName = $this->calculateDisplayName($entity, $entityClass); 
            $entity->setDisplayName($displayName); 

        }

        $this->databaseEm->flush();

        return true; 
    }

    /**
     * Get hierarchy path of an entityClass  
     *
     * @param EntityClass $entityClass
     *
     * @return String $path Hierarchy path of entityClass
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
     * Get parents of an entityClass. Include soft delete entityClasses
     *
     * @param EntityClass $entityClass
     *
     * @return array $parents
     */
    public function getPath(EntityClass $entityClass){

        $parents = array($entityClass); 
        $this->getParent($entityClass, $parents);

        return $this->orderedEntityClasssProperties($parents); 
    }

    /**
     * Get direct parent of an entityClass and add it to parents array
     *
     * @param EntityClass $entityClass
     * @param array $parents
     */
    private function getParent(EntityClass $entityClass, array &$parents){

        if($entityClass->getParent() != null){
            array_unshift($parents, $entityClass->getParent()); 
            $this->getParent($entityClass->getParent(), $parents);
        }
    }


    /**
     * Ordered properties for all entityClasses in 
     * $entityClasses array
     *
     * @param array $entityClasses
     *
     * @return array $orderedEntityClasss
     */
    public function orderedEntityClasssProperties(array $entityClasses){

        $orderedEntityClasses = array(); 

        foreach($entityClasses as $entityClass){
            $entityClass = $this->em->getRepository('SLCoreBundle:EntityClass')->fullFindById($entityClass->getId());
            $orderedEntityClasses[] = $entityClass;
        }

        return $orderedEntityClasses; 
    }
}
