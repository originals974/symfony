<?php

namespace SL\CoreBundle\Services;

//Symfony classes
use Doctrine\ORM\EntityManager;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Form\Form; 
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine;  
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Routing\Router; 

//Custom classes
use SL\CoreBundle\Entity\Object;
use SL\CoreBundle\Services\JSTreeService;

/**
 * Object Service
 *
 */
class ObjectService
{
    private $registry; 
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
        $this->registry = $registry;
        $this->em = $registry->getManager();
        $this->databaseEm = $registry->getManager('database');
        $this->translator = $translator;
        $this->formFactory = $formFactory;
        $this->router = $router;
    }

    /**
    * Create object form
    *
    * @param Object $object
    *
    * @return Form $form
    */
    public function createCreateForm(Object $object)
    {
        $parentObject = $object->getParent();

        //Disable parent combobox if object has a parent object
        if($object->getParent() != null) {
            $disabledParentField = true; 
        }
        else{
            $disabledParentField = false; 
        }

        $formType = ($object->isDocument())?'document':'object';

        $form = $this->formFactory->create($formType, $object, array(
            'action' => $this->router->generate('object_create', array(
                'isDocument' => $object->isDocument(),
                'id' =>  ( $parentObject != null)?$parentObject->getId():0,
                )
            ),
            'method' => 'POST',
            'submit_label' => 'create',
            'submit_color' => 'primary',
            'disabled_parent_field' => $disabledParentField,
            'object' => $object
            )
        );

        return $form;
    }

    /**
    * Update object form
    *
    * @param Object $object
    *
    * @return Form $form
    */
    public function createEditForm(Object $object)
    {    
        $formService = ($object->isDocument())?'document':'object';
        
        $form = $this->formFactory->create($formService, $object, array(
            'action' => $this->router->generate('object_update', array('id' => $object->getId())),
            'method' => 'PUT',
            'submit_label' => 'update',
            'submit_color' => 'primary',
            'disabled_parent_field' => false,
            'object' => $object
            )
        );

        return $form;
    }

    /**
    * Update Calculated name form 
    *
    * @param Object $object
    *
    * @return Form $form
    */
    public function createEditCalculatedNameForm(Object $object)
    {      
        $form = $this->formFactory->create('object_calculated_name', $object, array(
            'action' => $this->router->generate('object_update_calculated_name', array(
                'id' => $object->getId(),
                )
            ),
            )
        );

        return $form;
    }

    /**
     * Delete object Form
     *
     * @param Object $object
     *
     * @return Form $form
     */
    public function createDeleteForm(Object $object)
    {
        $formType = ($object->isDocument())?'document':'object';
        
        $form = $this->formFactory->create($formType, $object, array(
            'action' => $this->router->generate('object_delete', array('id' => $object->getId())),
            'method' => 'DELETE',
            'submit_label' => 'delete',
            'submit_color' => 'danger',
            'disabled_parent_field' => false,
            'object' => $object
            )
        );

        return $form;
    }

   /**
     * Verify integrity of an object before delete
     *
     * @param Object $object Object to delete
     *
     * @return Array $integrityError Title and error message
     */
    public function integrityControlBeforeDelete(Object $object) 
    {
        $integrityError = null;

        //Check if object is link to another
        $targetObject = $this->em->getRepository('SLCoreBundle:EntityProperty')->findByTargetObject($object);

        if($targetObject != null){
            $title = $this->translator->trans('delete.error.title');
            $message = $this->translator->trans('delete.object.reference.error.message');

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
     * by using calculatedName attribute of object
     *
     * @param Mixed $entity
     * @param Object $object
     *
     * @return String $displayName DisplayName of new entity
     */
    public function calculateDisplayName($entity, Object $object) 
    { 
        $patternString = $object->getCalculatedName();

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
     * Init calculated name for a new object
     *
     * @param Object $object Object
     */
    public function initCalculatedName(Object $object){

        if($object->getParent() != null){
            $object->setCalculatedName($object->getParent()->getCalculatedName());
        }
        else{
            $defaultProperty = $object->getProperties()->first(); 
            $object->setCalculatedName('%'.$defaultProperty->getTechnicalName().'%');
        }
        
        $this->em->flush(); 
    }

    /**
    * Refresh displayName of entity linked to object
    *
    * @param Object $object 
    *
    */
    public function refreshCalculatedName(Object $object){

        $entities = $this->databaseEm ->getRepository('SLDataBundle:'.$object->getTechnicalName())
                                ->findAll(); 

        foreach($entities as $entity) {

            $displayName = $this->calculateDisplayName($entity, $object); 
            $entity->setDisplayName($displayName); 

        }

        $this->databaseEm->flush();

        return true; 
    }

    /**
     * Get hierarchy path of an object  
     *
     * @param Object $object
     *
     * @return String $path Hierarchy path of object
     */
    public function getObjectPath($object){

        $objects = $this->getPath($object); 

        $path = "";
        foreach($objects as $key=>$object){
            if($key == 0) {
                $path = $object->getDisplayName();
            }
            else {
                $path = $path." -> ".$object->getDisplayName(); 
            }
        }

        return $path; 
    }

    /**
     * Get parents of an object. Include soft delete objects
     *
     * @param Object $object
     *
     * @return array $parents
     */
    public function getPath(Object $object){

        $parents = array($object); 
        $this->getParent($object, $parents);

        return $this->orderedObjectsProperties($parents); 
    }

    /**
     * Get direct parent of an object and add it to parents array
     *
     * @param Object $object
     * @param array $parents
     */
    private function getParent(Object $object, array &$parents){

        if($object->getParent() != null){
            array_unshift($parents, $object->getParent()); 
            $this->getParent($object->getParent(), $parents);
        }
    }


    /**
     * Ordered properties for all objects in 
     * $objects array
     *
     * @param array $objects
     *
     * @return array $orderedObjects
     */
    public function orderedObjectsProperties(array $objects){

        $orderedObjects = array(); 

        foreach($objects as $object){
            $object = $this->em->getRepository('SLCoreBundle:Object')->fullFindById($object->getId());
            $orderedObjects[] = $object;
        }

        return $orderedObjects; 
    }
}
