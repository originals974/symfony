<?php

namespace SL\CoreBundle\Controller;

//Symfony classes
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

//Custom classes
use SL\CoreBundle\Entity\Object;
use SL\CoreBundle\Entity\Property;

/**
 * Property controller
 *
 */
class PropertyController extends Controller
{
    private $em;
    private $propertyService;
    private $jstreeService;
    private $iconService;
    private $doctrineService;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "propertyService" = @DI\Inject("sl_core.property"),
     *     "jstreeService" = @DI\Inject("sl_core.js_tree"),
     *     "iconService" = @DI\Inject("sl_core.icon"),
     *     "doctrineService" = @DI\Inject("sl_core.doctrine")
     * })
     */
    public function __construct($em, $propertyService, $jstreeService, $iconService, $doctrineService)
    {
        $this->em = $em;
        $this->propertyService = $propertyService;
        $this->jstreeService = $jstreeService;
        $this->iconService = $iconService;
        $this->doctrineService = $doctrineService;
    }

    /**
     * Display form to create Property
     *
     * @param Object $object Parent Object Property
     *
     */
    public function newAction(Request $request, Object $object)
    {
        if ($request->isXmlHttpRequest()) {

            $property = new Property();
 
            $formArray = $this->createCreateForm($object, $property, 'default');
            $formChoice = $formArray['choiceForm']; 
            $form = $formArray['mainForm'];

            $response = $this->render('SLCoreBundle::save.html.twig', array(
                'entity' => $property,
                'formChoice' => $formChoice->createView(),
                'form'   => $form->createView(),
                )
            );
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }

        return $response; 
    }

    
    /**
     * Display form to choose Property type (default, entity, list) 
     *
     * @param Object $object Parent Object Property
     *
     */
    public function choiceFormAction(Request $request, Object $object)
    {
        if ($request->isXmlHttpRequest()) {

            //Get property type select by user 
            $formMode = $request->request->get('formMode'); 

            //Create Property object
            $property = $this->propertyService->getPropertyObjectByFormMode($formMode); 
            $property->setObject($object); 

            $formArray = $this->createCreateForm($object, $property, $formMode);
            $formChoice = $formArray['choiceForm']; 
            $form = $formArray['mainForm'];

            $response = $this->render('SLCoreBundle::save.html.twig', array(
                'entity' => $property,
                'formChoice' => $formChoice->createView(),
                'form'   => $form->createView(),
                )
            );
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }

        return $response;
    }

    /**
     * Create Form action
     *
     * @param Object $object Parent Object Property
     * @param String $formMode Property type to create (Default | Entity | List) 
     *
     * @ParamConverter("object", options={"repository_method" = "findFullById"})
     */
    public function createAction(Request $request, Object $object, $formMode)
    {
        $property = $this->propertyService->getPropertyObjectByFormMode($formMode); 
        $property->setObject($object);
        $object->addProperty($property);

        $formArray = $this->createCreateForm($object, $property, $formMode);
        $formChoice = $formArray['choiceForm'];
        $form = $formArray['mainForm'];

        $form->handleRequest($request);

        if ($request->isXmlHttpRequest()) {

            $isValid = $form->isValid();
            if ($isValid) {

                if($formMode == 'entity' || $formMode == 'data_list') {
                    $fieldType = $this->em->getRepository('SLCoreBundle:FieldType')->findOneByTechnicalName($formMode);
                    $property->setFieldType($fieldType); 
                }

                //Define Property display position
                $maxDiplayOrder = $this->em->getRepository('SLCoreBundle:Property')->findMaxDisplayOrder($object);
                $property->setDisplayOrder($maxDiplayOrder + 1);

                //Save Property in database
                $this->em->persist($property);
                $this->em->flush();

                //Dont delete this flush : Persist data after Doctrine evenement
                $this->em->flush();

                //Update database Object schema
                $this->doctrineService->doctrineGenerateEntityFileByObject($object);  
                $this->doctrineService->doctrineSchemaUpdateForce();
            }

            $jsonResponse = $this->propertyService->createJsonResponse($object, $property, $formChoice, $form); 

            $response = $jsonResponse;
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }

        return $response; 
    }

    /**
    * Create Property form
    *
    * @param Object $object Parent Object Property
    * @param Property $property Property to create
    * @param String $formMode Depending of the property type to create (Default | Entity | List) 
    *
    * @return Array $form Array of Form
    *
    */
    private function createCreateForm(Object $object, Property $property, $formMode)
    {   
        $form = array(); 

        $choiceForm = $this->createForm('property_choice', null, array(
            'action' => $this->generateUrl('property_choice_form', array(
                'id' => $object->getId(),
                )
            ),
            'method' => 'POST',
            )
        );

        $choiceForm->get('formMode')->setData($formMode);

        $form['choiceForm'] = $choiceForm; 

        //Select formType depending to formMode
        $formService = $this->propertyService->selectFormService($formMode); 

        $mainForm = $this->createForm($formService, $property, array(
            'action' => $this->generateUrl('property_create', array(
                    'id' => $object->getId(),
                    'formMode' => $formMode,
                )
            ),
            'method' => 'POST',
            'object_id' => $object->getId(),
            'submit_label' => 'create',
            'submit_color' => 'primary',
            )
        );

        $form['mainForm'] = $mainForm; 

        return $form;
    }

    /**
     * Display form to edit Property
     *
     * @param Property $property Property to update
     *
     */
    public function editAction(Property $property)
    {
        $form = $this->createEditForm($property);
 
        return $this->render('SLCoreBundle::save.html.twig', array(
            'entity' => $property,
            'form'   => $form->createView(),
            )
        );
    }

    /**
     * Update form action
     *
     * @param Property $property Property to update
     *
     */
    public function updateAction(Request $request, Property $property)
    {
        $form = $this->createEditForm($property);

        $form->handleRequest($request);

        if ($request->isXmlHttpRequest()) {

            $isValid = $form->isValid();
            if ($isValid) {

                $this->em->flush();
                      
                //Update database Object schema
                $this->doctrineService->doctrineGenerateEntityFileByObject($property->getObject());  
                $this->doctrineService->doctrineSchemaUpdateForce();

                $object = $this->em->getRepository('SLCoreBundle:Object')->findFullById($property->getObject()->getId()); 

                $html = $this->renderView('SLCoreBundle:Property:propertyTable.html.twig', array(
                    'object' => $object, 
                    )
                );

                $nodeStructure = $this->jstreeService->updatePropertyNode($property);
            }
            else {
                //Create form with errors
                $html = $this->renderView('SLCoreBundle::save.html.twig', array(
                    'entity' => $property,
                    'form'   => $form->createView(),
                    )
                );
                $nodeStructure = null;
            }

            $data = array(  
                'form' => array(
                    'action' => strtolower($form->getConfig()->getMethod()),
                    'isValid' => $isValid,
                    ), 
                'html' => $html,
                'node' => array(
                    'nodeStructure' => $nodeStructure,
                    'nodeProperties' => null,
                ),
            );    
            $response = new JsonResponse($data);
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }

        return $response; 
    }


    /**
    * Update Property form
    *
    * @param Property $property Property to update
    *
    * @return Form $form Update form
    *
    */
    private function createEditForm(Property $property)
    {
        //Select FormType depending to FieldType
        $formMode = $this->propertyService->getFormModeByProperty($property); 
        $formService = $this->propertyService->selectFormService($formMode); 

        $form = $this->createForm($formService, $property, array(
            'action' => $this->generateUrl('property_update', array(
                'id' => $property->getId(),
                )
            ),
            'method' => 'PUT',
            'object_id' => $property->getObject()->getId(),
            'submit_label' => 'update',
            'submit_color' => 'primary',
            )
        );

        return $form;
    }

    /**
     * Display form to remove Property
     *
     * @param Property $property Property to delete
     *
     */
    public function removeAction(Property $property)
    {
        //Property integrity control before delete
        $integrityError = $this->propertyService->integrityControlBeforeDelete($property); 
        if($integrityError == null) {
                   
            $form = $this->createDeleteForm($property);

            return $this->render('SLCoreBundle::save.html.twig', array(
                'entity' => $property,
                'form'   => $form->createView(),
                )
            );
        }
        else {

            //Create error modal window
            $response = $this->render('SLCoreBundle::errorModal.html.twig', array(
                'title' => $integrityError['title'],
                'message'   => $integrityError['message'],
                )
            );
        }

        return $response;
    }

    /**
     * Delete form action.
     *
     * @param Property $property Property to delete
     */
    public function deleteAction(Request $request, Property $property)
    {
        if ($request->isXmlHttpRequest()) {

            $nodeStructure = array(
                'id' => $property->getTechnicalName(),
            );

            $form = $this->createDeleteForm($property);

            $this->em->remove($property);
            $this->em->flush();

            //Update database Object schema
            $this->doctrineService->doctrineGenerateEntityFileByObject($property->getObject());  
            $this->doctrineService->doctrineSchemaUpdateForce();

            $object = $this->em->getRepository('SLCoreBundle:Object')->findFullById($property->getObject()->getId()); 

            $html = $this->renderView('SLCoreBundle:Property:propertyTable.html.twig', array(
                'object' => $object, 
                )
            );

            $data = array( 
                'form' => array(
                    'action' => strtolower($form->getConfig()->getMethod()),
                    'isValid' => true,
                    ),
                'html' => $html,
                'node' => array(
                    'nodeStructure' => $nodeStructure,
                    'nodeProperties' => null,
                ),
            );
            $response = new JsonResponse($data);
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }   

        return $response;    
    }


    /**
     * Delete Property Form
     *
     * @param Property $property Property to delete
     *
     * @return Form $form Delete form
     */
    private function createDeleteForm(Property $property)
    {
        $form = $this->createForm('property', $property, array(
            'action' => $this->generateUrl('property_delete', array(
                'id' => $property->getId(),
                )
            ),
            'method' => 'DELETE',
            'object_id' => $property->getObject()->getId(),
            'submit_label' => 'delete',
            'submit_color' => 'danger',
            )
        );

        return $form;
    }

    /**
     * Update Property checkbox.
     *
     * @param Property $property Property to update
     *
     */
    public function updateCheckboxAction(Request $request, Property $property)
    {
        if ($request->isXmlHttpRequest()) {

            $name = $request->request->get('name'); 
            $value = ($request->request->get('value')=='true')?true:false;

            switch ($name) {
                case 'isEnabled':
                    $property->setEnabled($value);
                    $response = new JsonResponse(
                        array(
                            'id' => $property->getTechnicalName(),
                            'icon' => $this->iconService->getPropertyIcon($property),
                            )
                        );
                    break;
                
                case 'isRequired':
                    $property->setRequired($value);
                    $response = new Response();
                    break;
            }
          
            $this->em->flush();

            if($name == 'isRequired') {
                 //Update database Object schema
                $this->doctrineService->doctrineGenerateEntityFileByObject($property->getObject());  
                $this->doctrineService->doctrineSchemaUpdateForce();
            }
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }   

        return $response;    
    }
}
