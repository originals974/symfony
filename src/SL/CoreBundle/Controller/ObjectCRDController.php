<?php

namespace SL\CoreBundle\Controller;

//Symfony classes
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use JMS\DiExtraBundle\Annotation as DI;

//Custom classes
use SL\CoreBundle\Entity\Object;
use SL\CoreBundle\Form\ObjectType;

/**
 * Object Create Read Delete controller
 *
 */
class ObjectCRDController extends Controller
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

            $object = new Object($parentObject, $isDocument, null);
 
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
        $defaultPropertyfieldType = ($parentObject==null)?$this->em->getRepository('SLCoreBundle:FieldType')->findOneByTechnicalName('text'):null;

        $object = new Object($parentObject, $isDocument, $defaultPropertyfieldType);

        $form = $this->createCreateForm($object, $parentObject, $isDocument);

        $form->handleRequest($request);
 
        if ($request->isXmlHttpRequest()) {
            
            if ($form->isValid()) {

                //Define Object display order
                $object->setDisplayOrder($this->em->getRepository('SLCoreBundle:Object')->findMaxDisplayOrder($parentObject) + 1); 

                $this->em->persist($object);
                $this->em->flush();

                //Dont delete this flush : Persist data after Doctrine evenement
                $this->em->flush();

                //Update database Object schema
                $this->doctrineService->updateObjectSchema($object); 
            }
 
            $jsonResponse = $this->objectService->createJsonResponse($object, $form);

            $response = $jsonResponse;
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
}
