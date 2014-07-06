<?php

namespace SL\CoreBundle\Controller;

//Symfony classes
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use JMS\DiExtraBundle\Annotation as DI;

//Custom classes
use SL\CoreBundle\Entity\Object;
use SL\CoreBundle\Entity\Property;
use SL\CoreBundle\Form\ObjectType;
use SL\CoreBundle\Form\ObjectCalculatedNameType;

/**
 * Object controller
 *
 */
class ObjectController extends Controller
{
    private $em;
    private $objectService;
    private $propertyService;
    private $jstreeService;
    private $iconService;
    private $classService;
    private $doctrineService;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "objectService" = @DI\Inject("sl_core.object"),
     *     "propertyService" = @DI\Inject("sl_core.property"),
     *     "jstreeService" = @DI\Inject("sl_core.jsTree"),
     *     "iconService" = @DI\Inject("sl_core.icon"),
     *     "classService" = @DI\Inject("sl_core.class"),
     *     "doctrineService" = @DI\Inject("sl_core.doctrine")
     * })
     */
    public function __construct($em, $objectService, $propertyService, $jstreeService, $iconService, $classService, $doctrineService)
    {
        $this->em = $em;
        $this->objectService = $objectService;
        $this->propertyService = $propertyService;
        $this->jstreeService = $jstreeService;
        $this->iconService = $iconService;
        $this->classService = $classService;
        $this->doctrineService = $doctrineService;
    }

    /**
     * Display object main screen
     *
     * @param boolean $isDocument True if object is a document
     *
     */
    public function indexAction(Request $request, $isDocument)
    {   
        if ($request->isXmlHttpRequest()) {

            $response = $this->render('SLCoreBundle:Object:index.html.twig',array(
                'isDocument' => $isDocument,
                )
            );
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }

        return $response; 
    }

    /**
    * Display form to create Object
    *
    * @param boolean $isDocument True if object is a document
    * @param Object $parentObject Parent object of the new Object
    *
    */
    public function newAction(Request $request, $isDocument, Object $parentObject=null)
    {
        if ($request->isXmlHttpRequest()) {

            $object = new Object($parentObject, $isDocument);
 
            $form = $this->createCreateForm($object, $parentObject, $isDocument);

            $response = $this->render('SLCoreBundle::save.html.twig', array(
                'entity' => $object,
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
     * @param boolean $isDocument True if object is a document
     * @param Object $parentObject Parent object of the new object
     *
     */
    public function createAction(Request $request,  $isDocument, Object $parentObject=null)
    {
        $object = new Object($parentObject, $isDocument);

        $form = $this->createCreateForm($object, $parentObject, $isDocument);

        $form->handleRequest($request);
 
        if ($request->isXmlHttpRequest()) {
            
            $isValid = $form->isValid();
            
            if ($isValid) {

                //Define Object display position
                $maxDiplayOrder = $this->em->getRepository('SLCoreBundle:Object')->findMaxDisplayOrder($parentObject);
                $object->setDisplayOrder($maxDiplayOrder + 1); 

                //Create default Property
                if($parentObject == null) {
                    $property = $this->propertyService->createDefaultPropertyName();
                    $property->setObject($object);
                    $object->addProperty($property);
                }
                else{
                    $property = null;
                }

                //Save Object and default Property in database
                $this->em->persist($object);
                $this->em->flush();

                //Define technicalName of Object
                $object->setTechnicalName($this->classService->getClassShortName($object));
                
                if($parentObject == null) {
                    $property->setTechnicalName($this->classService->getClassShortName($property));
                }

                $this->em->flush();

                //Update default calculated name of Object
                if($parentObject == null) {
                    $object->setCalculatedName('%'.$property->getTechnicalName().'%'); 
                }
                else{
                    $object->setCalculatedName($parentObject->getCalculatedName());
                }
                $this->em->flush();

                //Update database Object schema
                $this->doctrineService->updateObjectSchema($object); 

                //Create the Object node in menu tree
                if($parentObject == null) {
                    $parent = 'current.node'; 
                }
                else {
                    $parent = 'first.child.node'; 
                }

                $html = null; 
                $nodeStructure = $this->jstreeService->createNewObjectNode($object, $property, $parentObject, $isDocument);
                $nodeProperties = array(
                    'parent' => $parent,
                    'select' => true,  
                );
            }
            else {
                //Create form with errors
                $html = $this->renderView('SLCoreBundle::save.html.twig', array(
                    'entity' => $object,
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
            $response = new JsonResponse($data);
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }

        return $response; 
    }


    /**
    * Create Object form
    *
    * @param Object $object Object to create
    * @param Object $parentObject Parent object of the new object
    * @param boolean $isDocument True if Object is a document
    *
    * @return Form $form Create form
    */
    private function createCreateForm(Object $object, Object $parentObject=null, $isDocument)
    {
        if($parentObject != null){
            $routeParameter = array(
                'id' => $parentObject->getId()
                );
        }
        else {
            $routeParameter = array(); 
        }

        $routeParameter['isDocument'] = $isDocument; 
     
        $form = $this->createForm(new ObjectType(), $object, array(
            'action' => $this->generateUrl('object_create', $routeParameter),
            'method' => 'POST',
            )
        );
     
        $form->add('submit', 'submit', array(
            'label' => 'form.submit.create',
            'attr' => array('class'=>'btn btn-primary btn-sm'),
            )
        );

        return $form;
    }

   
    /**
    * Display form to edit Object
    *
    * @param Object $object Object to edit
    *
    */
    public function editAction(Object $object)
    {
        $form = $this->createEditForm($object);
 
        return $this->render('SLCoreBundle::save.html.twig', array(
            'entity' => $object,
            'form'   => $form->createView(),
            )
        );
    }

    /**
    * Update form action
    *
    * @param Object $object Object to update
    *
    * @ParamConverter("object", options={"repository_method" = "findFullById"})
    */
    public function updateAction(Request $request, Object $object)
    {
        $form = $this->createEditForm($object);

        $form->handleRequest($request);

        if ($request->isXmlHttpRequest()) {

            $isValid = $form->isValid();
            if ($isValid) {

                $this->em->flush();

                $html = $this->renderView('SLCoreBundle:Object:subObjectTable.html.twig', array(
                    'object' => $object, 
                    )
                );
                $nodeStructure = $this->jstreeService->updateObjectNode($object);
            }
            else {
                //Create form with errors
                $html = $this->renderView('SLCoreBundle::save.html.twig', array(
                    'entity' => $object,
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
    * Update Object form
    *
    * @param Object $object Object to update
    *
    * @return Form $form Update form
    */
    private function createEditForm(Object $object)
    {    
        $form = $this->createForm(new ObjectType(), $object, array(
            'action' => $this->generateUrl('object_update', array('id' => $object->getId())),
            'method' => 'PUT',
            )
        );
     
        $form->add('submit', 'submit', array(
            'label' => 'update',
            'attr' => array('class'=>'btn btn-primary btn-sm'),
            )
        );

        return $form;
    }

     /**
     * Show an Object
     *
     * @param Object $object Object to show
     *
     * @ParamConverter("object", options={"repository_method" = "findFullById"})
     */
    public function showAction(Request $request, Object $object)
    {
        if ($request->isXmlHttpRequest()) {

            $response = $this->render('SLCoreBundle:Object:show.html.twig', array(
                'object' => $object, 
                )
            );
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }

        return $response; 
    }

    /**
    * Display form to remove Object.
    *
    * @param Object $object Object to remove
    *
    */
    public function removeAction(Object $object)
    {
        //Object integrity control before delete
        $integrityError = $this->objectService->integrityControlBeforeDelete($object); 
        if($integrityError == null) {
                   
            $form = $this->createDeleteForm($object);

            $response = $this->render('SLCoreBundle::save.html.twig', array(
                'entity' => $object,
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
     * @param Object $object Object to delete
     *
     */
    public function deleteAction(Request $request, Object $object)
    {
        if ($request->isXmlHttpRequest()) {

            $nodeStructure = array(
                'id' => $object->getTechnicalName(),
            );

            $form = $this->createDeleteForm($object);

            $this->em->remove($object);
            $this->em->flush();

            //Update database Object schema
            $this->doctrineService->deleteObjectSchema($object);

            $data = array(  
                'form' => array(
                    'action' => strtolower($form->getConfig()->getMethod()),
                    'isValid' => true,
                    ),
                'html' => null,
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
     * Delete Object Formt
     *
     * @param Object $object Object to delete
     *
     * @return Form $form Delete form
     */
    private function createDeleteForm(Object $object)
    {
        $method = 'DELETE'; 

        $form = $this->createForm(new ObjectType($method), $object, array(
            'action' => $this->generateUrl('object_delete', array('id' => $object->getId())),
            'method' => $method,
            )
        );

        $form->add('submit', 'submit', array(
            'label' => 'delete',
            'attr' => array('class'=>'btn btn-danger btn-sm'),
            )
        );

        return $form;
    }

    /**
     * Update Object icon.
     *
     * @param Object $object Object to update
     *
     */
    public function updateIconAction(Request $request, Object $object)
    {
        if ($request->isXmlHttpRequest()) {

            $icon = $request->request->get('icon'); 

            if($icon != $object->getIcon()) {
                $object->setIcon($icon); 
                $this->em->flush();
            }

            $data = array(  
                'id' => $object->getTechnicalName(),
                'icon' => $this->iconService->getObjectIcon($object),
            );
            $response = new JsonResponse($data);
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }   

        return $response;    
    }

    /**
     * Update Object checkbox.
     *
     * @param Object $object Object to update
     *
     */
    public function updateCheckboxAction(Request $request, Object $object)
    {
        if ($request->isXmlHttpRequest()) {

            $value = ($request->request->get('value')=='true')?true:false;

            $object->setIsEnabled($value);     
            $this->em->flush();

            $response = new JsonResponse(
                array(
                    'id' => $object->getTechnicalName(),
                    'icon' => $this->iconService->getObjectIcon($object),
                    )
                );
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }   

        return $response;    
    }

    /**
    * Display form to edit calculated name of an Object
    *
    * @param Object $object Object to update
    *
    * @ParamConverter("object", options={"repository_method" = "findFullById"})
    */
    public function editCalculatedNameAction(Object $object)
    {
        $form = $this->createEditCalculatedNameForm($object);
 
        return $this->render('SLCoreBundle:Object:objectNameDesigner.html.twig', array(
            'object' => $object,
            'form'   => $form->createView(),
            )
        );
    }

    /**
    * Edit calculated name form action.
    *
    * @param Object $object Object to update
    *
    */
    public function updateCalculatedNameAction(Request $request, Object $object)
    {
        $form = $this->createEditCalculatedNameForm($object);

        $form->handleRequest($request);

        if ($request->isXmlHttpRequest()) {

            $isValid = $form->isValid();
            if ($isValid) {

                $html = null; 
                $this->em->flush();
            }
            else {
                //Create form with errors
                $html = $this->renderView('SLCoreBundle:Object:objectNameDesigner.html.twig', array(
                    'object' => $object,
                    'form'   => $form->createView(),
                    )
                );
            }
            
            $data = array(  
                'form' => array(
                    'action' => strtolower($form->getConfig()->getMethod()),
                    'isValid' => $isValid,
                    ), 
                'html' => $html,
                'node' => null,
            );

            $response = new JsonResponse($data);
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }

        return $response; 
    }

    /**
    * Update Calculated name form 
    *
    * @param Object $object Object to update
    *
    * @return Form $form Update calculated name form
    */
    private function createEditCalculatedNameForm(Object $object)
    {      
        $form = $this->createForm(new ObjectCalculatedNameType(), $object, array(
            'action' => $this->generateUrl('object_update_calculated_name', array('id' => $object->getId())),
            'method' => 'PUT',
            )
        );
     
        $form->add('submit', 'submit', array(
            'label' => 'update',
            'attr' => array('class'=>'btn btn-primary btn-sm'),
            )
        );

        return $form;
    }

    /**
    * Refresh displayName of entity linked to Object
    *
    * @param Object $object Object 
    *
    */
    public function refreshCalculatedNameAction(Object $object){

        $databaseEm = $this->getDoctrine()->getManager('database');
        
        $entities = $databaseEm ->getRepository('SLDataBundle:'.$object->getTechnicalName())
                                ->findAll(); 

        foreach($entities as $entity) {

            $displayName = $this->objectService->calculateDisplayName($entity, $object); 
            $entity->setDisplayName($displayName); 

        }

        $databaseEm->flush();

        return $this->redirect($this->generateUrl('back_end')); 
    }
}
