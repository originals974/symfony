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
    private $doctrineService;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "objectService" = @DI\Inject("sl_core.object"),
     *     "propertyService" = @DI\Inject("sl_core.property"),
     *     "jstreeService" = @DI\Inject("sl_core.js_tree"),
     *     "iconService" = @DI\Inject("sl_core.icon"),
     *     "doctrineService" = @DI\Inject("sl_core.doctrine")
     * })
     */
    public function __construct($em, $objectService, $propertyService, $jstreeService, $iconService, $doctrineService)
    {
        $this->em = $em;
        $this->objectService = $objectService;
        $this->propertyService = $propertyService;
        $this->jstreeService = $jstreeService;
        $this->iconService = $iconService;
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
    *
    */
    public function newAction(Request $request, $isDocument)
    {
        if ($request->isXmlHttpRequest()) {

            $object = new Object($isDocument, null);
 
            $form = $this->createCreateForm($object, $isDocument);

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
     *
     */
    public function createAction(Request $request,  $isDocument)
    {
        $defaultPropertyfieldType = $this->em->getRepository('SLCoreBundle:FieldType')->findOneByTechnicalName('text');

        $object = new Object($isDocument, $defaultPropertyfieldType);

        $form = $this->createCreateForm($object, $isDocument);

        $form->handleRequest($request);
 
        if ($request->isXmlHttpRequest()) {
            
            if ($form->isValid()) {

                //Define Object display order
                $object->setDisplayOrder($this->em->getRepository('SLCoreBundle:Object')->findMaxDisplayOrder($isDocument) + 1); 

                $this->em->persist($object);
                $this->em->flush();

                //Dont delete this flush : Persist data after Doctrine evenement
                $this->em->flush();

                //Update database Object schema
                $this->doctrineService->doctrineGenerateEntityFileByObject($object);  
                $this->doctrineService->doctrineSchemaUpdateForce();
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
    * @param boolean $isDocument True if Object is a document
    *
    * @return Form $form Create form
    */
    private function createCreateForm(Object $object, $isDocument)
    {

        $form = $this->createForm(new ObjectType(), $object, array(
            'action' => $this->generateUrl('object_create', array(
                'isDocument' => $isDocument
                )
            ),
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
    public function showAction(Request $request,Object $object)
    {
        if ($request->isXmlHttpRequest()) {

            $path = $this->objectService->getObjectPath($object); 

            $response = $this->render('SLCoreBundle:Object:show.html.twig', array(
                'path' => $path,
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

            //Remove all Property of Object
            foreach ($object->getProperties() as $property) {
                $this->em->remove($property); 
            }

            $this->em->flush(); 

            $this->em->getRepository('SLCoreBundle:Object')->removeFromTree($object);
            $this->em->clear(); 

            //Update database Object schema
             $this->doctrineService->removeDoctrineFiles($object);

            //Get direct children of parent Object
            $directChildren = $this->em->getRepository('SLCoreBundle:Object')->children($object->getParent(), true); 

            foreach ($directChildren as $objectChild) {
                $this->doctrineService->doctrineGenerateEntityFileByObject($objectChild);  
            }
            
            $this->doctrineService->doctrineSchemaUpdateForce();

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
     * Delete Object Form
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
