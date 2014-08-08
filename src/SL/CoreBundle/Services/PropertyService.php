<?php

namespace SL\CoreBundle\Services;

//Symfony classes
use Symfony\Component\Translation\Translator;
use Symfony\Component\Form\Form; 
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Routing\Router;   

//Custom classes
use SL\CoreBundle\Entity\EntityClass;
use SL\CoreBundle\Entity\Property;
use SL\CoreBundle\Entity\PropertyEntity;
use SL\CoreBundle\Entity\PropertyChoice;

/**
 * Property Service
 *
 */
class PropertyService
{
    private $translator;
    private $formFactory;
    private $router;

    /**
     * Constructor
     *
     * @param Translator $translator
     * @param FormFactory $formFactory
     * @param Router $router
     *
     */
    public function __construct(Translator $translator, FormFactory $formFactory, Router $router)
    {
        $this->translator = $translator;
        $this->formFactory = $formFactory;
        $this->router = $router;
    }

     /**
    * Create property form
    *
    * @param EntityClass $entityClass Parent entityClass of new property
    * @param Property $property 
    * @param String $formMode Depending of the property type to create (Default | Entity | List) 
    *
    * @return Array $form Array of form
    */
    public function createCreateForm(EntityClass $entityClass, Property $property, $formMode)
    {   
        $form = array(); 

        $selectForm = $this->formFactory->create('sl_core_property_select', null, array(
            'action' => $this->router->generate('property_select_form', array(
                'id' => $entityClass->getId(),
                )
            ),
            'method' => 'POST',
            )
        );

        $selectForm->get('formMode')->setData($formMode);

        $form['selectForm'] = $selectForm; 

        //Select formType depending to formMode
        $formService = $this->selectFormService($formMode); 

        $mainForm = $this->formFactory->create($formService, $property, array(
            'action' => $this->router->generate('property_create', array(
                    'id' => $entityClass->getId(),
                    'formMode' => $formMode,
                )
            ),
            'method' => 'POST',
            'submit_label' => 'create',
            'submit_color' => 'primary',
            )
        );

        $form['mainForm'] = $mainForm; 

        return $form;
    }

    /**
    * Update property form
    *
    * @param Property $property
    *
    * @return Form $form
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
            'submit_label' => 'update',
            'submit_color' => 'primary',
            )
        );

        return $form;
    }

     /**
     * Delete property form
     *
     * @param Property $property
     *
     * @return Form $form
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
            'submit_label' => 'delete',
            'submit_color' => 'danger',
            )
        );

        return $form;
    }

   /**
     * Select the FormType
     *
     * @param String $formMode Default|Entity|List
     *
     * @return Mixed formType The form type
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

        return $formService ; 
    }

    /**
     * Select property entity class
     *
     * @param String $formMode Default|Entity|List
     *
     * @return Mixed $property
     */
    public function getPropertyEntityClassByFormMode($formMode) 
    {
        switch($formMode) {
            case 'entity' : 
                $property = new PropertyEntity();
                break; 
            case 'choice' : 
                $property = new PropertyChoice();
                break;
            default:
                $property = new Property();
        }

        return $property; 
    }

    /**
     * Find the formMode for a property
     *
     * @param Property $property
     *
     * @return String $formMode Default|Entity|List
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
     * Verify integrity of a property before delete
     *
     * @param Property $property Property to delete
     *
     * @return Array $integrityError Title and error message
     */
    public function integrityControlBeforeDelete(Property $property) 
    {
        $integrityError = null;

        //Check if property is used in EntityClass calculatedName pattern
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
