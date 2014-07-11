<?php

namespace SL\CoreBundle\Services;

//Symfony classes
use Doctrine\ORM\EntityManager;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Form\Form; 
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine;  

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
    private $jstreeService;
    private $templating;

    /**
     * Constructor
     *
     * @param EntityManager $em
     * @param Translator $translator
     * @param JSTreeService $jstreeService
     * @param TimedTwigEngine $templating
     *
     */
    public function __construct(EntityManager $em, Translator $translator, JSTreeService $jstreeService, TimedTwigEngine $templating)
    {
        $this->em = $em;
        $this->translator = $translator;
        $this->jstreeService = $jstreeService;
        $this->templating = $templating;
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

    /**
     * Create JsonResponse for Property creation  
     *
     * @param Object $object Parent Object of new property
     * @param Property $property Created Property
     * @param Form $formChoice PropertyFormChoice form
     * @param Form $form Creation PropertyType form
     *
     * @return JsonResponse
     */
    public function createJsonResponse(Object $object, Property $property, Form $formChoice, Form $form) {

        $isValid = $form->isValid();  

        if($isValid) {
            $html = $this->templating->render('SLCoreBundle:Property:propertyTable.html.twig', array(
                'object' => $object, 
                )
            );

            //Create the Property node in menu tree 
            $nodeStructure = $this->jstreeService->createNewPropertyNode($property);
            $nodeProperties = array(
                'parent' => 'current.node',
                'select' => false,  
            );
        }
        else {
            //Create form with errors 
            $html = $this->templating->render('SLCoreBundle::save.html.twig', array(
                'entity' => $property,
                'formChoice' => $formChoice->createView(),
                'form'   => $form->createView(),
                )
            ); 

            $nodeStructure = null; 
            $nodeProperties = null; 
        }

        $data = array(  
            'form' => array(
                'action' => strtolower($form->getConfig()->getMethod()),
                'isValid' => $isValid,
                ),
            'html' => $html,
            'node' => array(
                'nodeStructure' => $nodeStructure,
                'nodeProperties' => $nodeProperties,
            ),
        );

        return new JsonResponse($data); 
    }
}
