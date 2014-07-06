<?php

namespace SL\CoreBundle\Services;

//Symfony classes
use Doctrine\ORM\EntityManager;
use Symfony\Component\Translation\Translator;

//Custom classes
use SL\CoreBundle\Form\PropertyType;
use SL\CoreBundle\Form\EntityPropertyType;
use SL\CoreBundle\Form\ListPropertyType;
use SL\CoreBundle\Entity\Object;
use SL\CoreBundle\Entity\Property;
use SL\CoreBundle\Entity\EntityProperty;
use SL\CoreBundle\Entity\ListProperty;

/**
 * Property Service
 *
 */
class PropertyService
{
    private $em;
    private $translator;

    /**
     * Constructor
     *
     * @param EntityManager $em
     * @param Translator $translator
     *
     */
    public function __construct(EntityManager $em, Translator $translator)
    {
        $this->em = $em;
        $this->translator = $translator;
    }

   /**
     * Select the FormType
     *
     * @param String $formMode Default|Entity|List
     *
     * @return Mixed formType The form type
     */
    public function selectFormType($formMode, Object $object) 
    {
        switch($formMode) {
            case 'entity' : 
                $formType = new EntityPropertyType($object);
                break; 
            case 'data_list' : 
                $formType = new ListPropertyType();
                break; 
            default:
                $formType = new PropertyType();
        }

        return $formType ; 
    }

    /**
     * Select the Property object
     *
     * @param String $formMode Default|Entity|List
     *
     * @return Mixed $property
     */
    public function getPropertyObjectByFormMode($formMode) 
    {
        switch($formMode) {
            case 'entity' : 
                $property = new EntityProperty();
                break; 
            case 'data_list' : 
                $property = new ListProperty();
                break;
            default:
                $property = new Property();
        }

        return $property; 
    }

    /**
     * Find the formMode for a Property
     *
     * @param Property $property Property
     *
     * @return String $formMode Default|Entity|List
     */
    public function getFormModeByProperty(Property $property) 
    {
        $fieldTypeTechnicalName = $property->getFieldType()->getTechnicalName(); 

        switch($fieldTypeTechnicalName) {
            case 'entity' : 
            case 'data_list' : 
                $formMode = $fieldTypeTechnicalName; 
                break; 
            default:
                $formMode = 'default';
        }

        return $formMode; 
    }

    /**
     * Creates a default Property called "Name" for an object
     *
     * @return Property $property The default Property 
     */
    public function createDefaultPropertyName() 
    {
        //Variables initialisation 
        $property = new Property(); 

        //Get FielType text
        $FieldType = $this->em->getRepository('SLCoreBundle:FieldType')->findOneByTechnicalName('text');

        //Initialised Property
        $property->setDisplayName('Nom');
        $property->setDisplayOrder(1);
        $property->setIsRequired(true);
        $property->setFieldType($FieldType);

        return $property; 
    }

    /**
     * Verify integrity of an Property before delete
     *
     * @param Property $property Property to delete
     *
     * @return Array $integrityError Title and error message
     */
    public function integrityControlBeforeDelete(Property $property) 
    {
        $integrityError = null;

        //Check if Property is used in Object calculatedName pattern
        $calculatedNamePattern = $property->getObject()->getCalculatedName(); 

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
