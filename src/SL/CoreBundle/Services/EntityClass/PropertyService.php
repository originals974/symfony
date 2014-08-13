<?php

namespace SL\CoreBundle\Services\EntityClass;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Routing\Router;   
use Symfony\Component\Form\Form; 

use SL\CoreBundle\Entity\EntityClass\EntityClass;
use SL\CoreBundle\Entity\EntityClass\Property;
use SL\CoreBundle\Entity\EntityClass\PropertyEntity;
use SL\CoreBundle\Entity\EntityClass\PropertyChoice;

/**
 * Property Service
 *
 */
class PropertyService
{
    private $em; 
    private $translator;
    private $formFactory;
    private $router;

    /**
     * Constructor
     *
     * @param EntityManager $em
     * @param Translator $translator
     * @param FormFactory $formFactory
     * @param Router $router
     *
     */
    public function __construct(EntityManager $em, Translator $translator, FormFactory $formFactory, Router $router)
    {
        $this->em = $em; 
        $this->translator = $translator;
        $this->formFactory = $formFactory;
        $this->router = $router;
    }

    /**
    * Create create form for $property
    * associated to $entityClass
    *
    * @param SL\CoreBundle\Entity\EntityClass\EntityClass $entityClass
    * @param SL\CoreBundle\Entity\EntityClass\Property $property
    * @param string $formMode|"default" Define what property type will be create(default|entity|choice)
    *
    * @return Symfony\Component\Form\Form $form
    */
    public function createCreateForm(EntityClass $entityClass, Property $property, $formMode="default")
    {   
        $form = array(); 

        $selectForm = $this->formFactory->create('sl_core_property_select', null, array(
            'action' => $this->router->generate('property_select_form', array(
                'entity_class_id' => $entityClass->getId(),
                )
            ),
            'method' => 'GET',
            )
        );

        $selectForm->get('formMode')->setData($formMode);

        $form['selectForm'] = $selectForm; 

        //Select $formService depending to $formMode
        $formService = $this->selectFormService($formMode); 

        $mainForm = $this->formFactory->create($formService, $property, array(
            'action' => $this->router->generate('property_create', array(
                    'entity_class_id' => $entityClass->getId(),
                    'formMode' => $formMode,
                )
            ),
            'method' => 'POST',
            'attr' => array(
                'valid-target' => 'property-content', 
                'no-valid-target' => 'ajax-modal',
                'mode' => 'add',  
                
                ),
            'submit_label' => 'create',
            'submit_color' => 'primary',
            'entity_class_id' => $entityClass->getId(),
            )
        );

        $form['mainForm'] = $mainForm; 

        return $form;
    }

    /**
    * Create update form for $property
    * associated to $entityClass
    *
    * @param SL\CoreBundle\Entity\EntityClass\EntityClass $entityClass
    * @param SL\CoreBundle\Entity\EntityClass\Property $property
    *
    * @return Symfony\Component\Form\Form $form
    */
    public function createEditForm(EntityClass $entityClass, Property $property)
    {
        //Select formtype depending to fieldtype
        $formMode = $this->getFormModeByProperty($property); 
        $formService = $this->selectFormService($formMode); 

        $form = $this->formFactory->create($formService, $property, array(
            'action' => $this->router->generate('property_update', array(
                'entity_class_id' => $entityClass->getId(),
                'id' => $property->getId(),
                )
            ),
            'method' => 'PUT',
            'attr' => array(
                'valid-target' => 'property-content', 
                'no-valid-target' => 'ajax-modal',
                'mode' => 'update',  
                
                ),
            'submit_label' => 'update',
            'submit_color' => 'primary',
            'entity_class_id' => $entityClass->getId(),
            )
        );

        return $form;
    }

    /**
    * Create update form for $property
    * associated to $entityClass
    *
    * @param SL\CoreBundle\Entity\EntityClass\EntityClass $entityClass
    * @param SL\CoreBundle\Entity\EntityClass\Property $property
    *
    * @return Symfony\Component\Form\Form $form
    */
    public function createDeleteForm(EntityClass $entityClass, Property $property)
    {
        $form = $this->formFactory->create('sl_core_property', $property, array(
            'action' => $this->router->generate('property_delete', array(
                'entity_class_id' => $entityClass->getId(),
                'id' => $property->getId(),
                )
            ),
            'method' => 'DELETE',
            'attr' => array(
                'valid-target' => 'property-content', 
                'no-valid-target' => 'ajax-modal',
                'mode' => 'delete',  
                
                ),
            'submit_label' => 'delete',
            'submit_color' => 'danger',
            'entity_class_id' => $entityClass->getId(),
            )
        );

        return $form;
    }

   /**
     * Get $formService by $formMode
     *
     * @param string $formMode default|entity|choice
     *
     * @return string $formService
     */
    public function selectFormService($formMode) 
    {
        switch($formMode) {
            case 'entity' : 
                $formService = 'sl_core_property_entity';
                break; 
            case 'choice' : 
                $formService = 'sl_core_property_choice';
                break; 
            default:
                $formService = 'sl_core_property';
        }

        return $formService; 
    }

    /**
     * Get property entity class by $formMode
     *
     * @param string $formMode default|entity|choice
     *
     * @return Mixed $property
     */
    public function getPropertyEntityClassByFormMode($formMode) 
    {
        $fieldType = $this->em->getRepository('SLCoreBundle:Field\FieldType')->findOneByFormType($formMode);
        
        switch($formMode) {
            case 'entity' : 
                $property = new PropertyEntity($fieldType);
                break; 
            case 'choice' : 
                $property = new PropertyChoice($fieldType);
                break;
            default:
                $property = new Property();
        }

        return $property; 
    }

    /**
     * Get $formMode for $property
     *
     * @param SL\CoreBundle\Entity\EntityClass\Property $property
     *
     * @return String $formMode default|entity|choice
     */
    public function getFormModeByProperty(Property $property) 
    {
        $fieldTypeFormType = $property->getFieldType()->getFormType(); 

        switch($fieldTypeFormType) {
            case 'entity' : 
            case 'choice' : 
                $formMode = $fieldTypeFormType; 
                break; 
            default:
                $formMode = 'default';
        }

        return $formMode; 
    }

    /**
     * Verify integrity of $property before delete
     *
     * @param SL\CoreBundle\Entity\EntityClass\Property $property
     *
     * @return array $integrityError Title and error message
     */
    public function integrityControlBeforeDelete(Property $property) 
    {
        $integrityError = null;

        //Check if $propert is used in EntityClass calculatedName pattern
        $calculatedNamePattern = $property->getEntityClass()->getCalculatedName(); 

        if(strpos(strtolower($calculatedNamePattern), strtolower($property->getTechnicalName())) !== false) {

            $title = $this->translator->trans('delete.error.title');
            $message = $this->translator->trans('delete.property.calculatedName.error.message');

            $integrityError = array(
                'title' => $title,
                'message' => $message,
                );

            return $integrityError;

        }

        return $integrityError; 
    }
}
