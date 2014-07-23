<?php

namespace SL\CoreBundle\Controller;

//Symfony classes
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\DiExtraBundle\Annotation as DI;
use Doctrine\ORM\EntityManager;

//Custom classes
use SL\CoreBundle\Entity\Object;
use SL\CoreBundle\Entity\Property;
use SL\CoreBundle\Services\PropertyService;
use SL\CoreBundle\Services\JSTreeService;
use SL\CoreBundle\Services\IconService;
use SL\CoreBundle\Services\DoctrineService;

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
    public function __construct(EntityManager $em, PropertyService $propertyService, JSTreeService $jstreeService, IconService $iconService, DoctrineService $doctrineService)
    {
        $this->em = $em;
        $this->propertyService = $propertyService;
        $this->jstreeService = $jstreeService;
        $this->iconService = $iconService;
        $this->doctrineService = $doctrineService;
    }

    /**
     * Display form to create property entity
     *
     * @param Object $object Parent object of new property
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
     * Display form to choose property type (default, entity, list) 
     *
     * @param Object $object Parent object of new property
     */
    public function choiceFormAction(Request $request, Object $object)
    {
        if ($request->isXmlHttpRequest()) {

            $formMode = $request->request->get('formMode'); 

            //Create property 
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
     * Create property entity
     *
     * @param Object $object Parent object of new property
     * @param String $formMode Property type to create (Default | Entity | List) 
     */
    public function createAction(Request $request, Object $object, $formMode)
    {
        $property = $this->propertyService->getPropertyObjectByFormMode($formMode); 
        $property->setObject($object);

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

                $this->em->persist($property);
                $this->em->flush();

                //Update database schema
                $this->doctrineService->doctrineGenerateEntityFileByObject($object);  
                $this->doctrineService->doctrineSchemaUpdateForce();

                $html = $this->renderView('SLCoreBundle:Property:propertyTable.html.twig', array(
                    'object' => $object, 
                    )
                );
            }
            else {
                $html = $this->renderView('SLCoreBundle::save.html.twig', array(
                    'entity' => $property,
                    'formChoice' => $formChoice->createView(),
                    'form'   => $form->createView(),
                    )
                ); 
            }
            
            $arrayResponse = array(
                'isValid' => $isValid,
                'content' => array(
                    'html' => $html,
                    'js_tree' => null,
                    ),
                );
 
            $response = new JsonResponse($arrayResponse); 
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }

        return $response; 
    }

    /**
    * Create property form
    *
    * @param Object $object Parent object of new property
    * @param Property $property 
    * @param String $formMode Depending of the property type to create (Default | Entity | List) 
    *
    * @return Array $form Array of form
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
     * Display form to edit property entity
     *
     * @param Property $property
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
     * Update property entity
     *
     * @param Property $property Property to update
     */
    public function updateAction(Request $request, Property $property)
    {
        $form = $this->createEditForm($property);

        $form->handleRequest($request);

        if ($request->isXmlHttpRequest()) {

            $isValid = $form->isValid();
            if ($isValid) {

                $this->em->flush();
                      
                //Update database schema
                $this->doctrineService->doctrineGenerateEntityFileByObject($property->getObject());  
                $this->doctrineService->doctrineSchemaUpdateForce();

                $object = $this->em->getRepository('SLCoreBundle:Object')->findFullById($property->getObject()->getId()); 

                $html = $this->renderView('SLCoreBundle:Property:propertyTable.html.twig', array(
                    'object' => $object, 
                    )
                );
            }
            else {
                $html = $this->renderView('SLCoreBundle::save.html.twig', array(
                    'entity' => $property,
                    'form'   => $form->createView(),
                    )
                );
            }

            $arrayResponse = array(
                'isValid' => $isValid,
                'content' => array(
                    'html' => $html,
                    'js_tree' => null,
                    ),
                );
 
            $response = new JsonResponse($arrayResponse); 
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }

        return $response; 
    }


    /**
    * Update property form
    *
    * @param Property $property
    *
    * @return Form $form
    */
    private function createEditForm(Property $property)
    {
        //Select formtype depending to fieldtype
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
     * Display form to remove property entity
     *
     * @param Property $property
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
            $response = $this->render('SLCoreBundle::errorModal.html.twig', array(
                'title' => $integrityError['title'],
                'message'   => $integrityError['message'],
                )
            );
        }

        return $response;
    }

    /**
     * Delete property entity
     *
     * @param Property $property Property to delete
     */
    public function deleteAction(Request $request, Property $property)
    {
        if ($request->isXmlHttpRequest()) {

            $this->em->remove($property);
            $this->em->flush();

            //Update database schema
            $this->doctrineService->doctrineGenerateEntityFileByObject($property->getObject());  
            $this->doctrineService->doctrineSchemaUpdateForce();

            $object = $this->em->getRepository('SLCoreBundle:Object')->findFullById($property->getObject()->getId()); 

            $html = $this->renderView('SLCoreBundle:Property:propertyTable.html.twig', array(
                'object' => $object, 
                )
            );

            $arrayResponse = array(
                'isValid' => true,
                'content' => array(
                    'html' => $html,
                    'js_tree' => null,
                    ),
                );
 
            $response = new JsonResponse($arrayResponse); 
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }

        return $response; 
    }


    /**
     * Delete property form
     *
     * @param Property $property
     *
     * @return Form $form
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
     * Update property checkbox
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
                    break;
                
                case 'isRequired':
                    $property->setRequired($value);
                    break;
            }
          
            $this->em->flush();

            if($name == 'isRequired') {
                //Update database schema
                $this->doctrineService->doctrineGenerateEntityFileByObject($property->getObject());  
                $this->doctrineService->doctrineSchemaUpdateForce();
            }

            $response = new Response();
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }   

        return $response;    
    }
}
