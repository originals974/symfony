<?php

namespace SL\CoreBundle\Controller;

//Symfony classes
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;

//Custom classes
use SL\CoreBundle\Entity\Object;
use SL\CoreBundle\Entity\Property;
use SL\CoreBundle\Services\ObjectService;
use SL\CoreBundle\Services\PropertyService;
use SL\CoreBundle\Services\JSTreeService;
use SL\CoreBundle\Services\IconService;
use SL\CoreBundle\Services\DoctrineService;

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
    public function __construct(EntityManager $em, ObjectService $objectService, PropertyService $propertyService, JSTreeService $jstreeService, IconService $iconService, DoctrineService $doctrineService)
    {
        $this->em = $em;
        $this->objectService = $objectService;
        $this->propertyService = $propertyService;
        $this->jstreeService = $jstreeService;
        $this->iconService = $iconService;
        $this->doctrineService = $doctrineService;
    }

    /**
     * Display create screen
     *
     * @param boolean $isDocument True if object is a document
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
    * Display form to create object entity
    *
    * @param boolean $isDocument True if object is a document
    * @param Object $parentObject Parent object of new object
    */
    public function newAction(Request $request, $isDocument, Object $parentObject = null)
    {
        if ($request->isXmlHttpRequest()) {

            $object = new Object($isDocument, null, $parentObject);
 
            $form = $this->createCreateForm($object);

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
     * Create object entity
     *
     * @param boolean $isDocument True if object is a document
     * @param Object $parentObject Parent object of new object
     */
    public function createAction(Request $request, $isDocument, Object $parentObject = null)
    {
        //Get text fieldtype if necessary
        if($parentObject == null) {
            $fieldType = $this->em->getRepository('SLCoreBundle:FieldType')->findOneByTechnicalName('text');
        }
        else{
            $fieldType = null; 
        }
            
        $object = new Object($isDocument, $fieldType, $parentObject);

        $form = $this->createCreateForm($object);

        $form->handleRequest($request);
 
        if ($request->isXmlHttpRequest()) {
            
            $isValid = $form->isValid();
            if ($isValid) {

                $this->em->persist($object);
                $this->em->flush();

                $this->objectService->initCalculatedName($object); 

                //Update database schema
                $this->doctrineService->doctrineGenerateEntityFileByObject($object);  
                $this->doctrineService->doctrineSchemaUpdateForce();

                $html = null; 
                $jsTree = $this->jstreeService->createNewObjectNode($object, $object->isDocument());
            
            }
            else {
                $jsTree = null; 
                $html = $this-->renderView('SLCoreBundle::save.html.twig', array(
                    'entity' => $object,
                    'form'   => $form->createView(),
                    )
                ); 
            }
 
            $arrayResponse = array(
                'isValid' => $isValid,
                'content' => array(
                    'html' => $html,
                    'js_tree' => $jsTree,
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
    * Create object form
    *
    * @param Object $object
    *
    * @return Form $form
    */
    private function createCreateForm(Object $object)
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

        $form = $this->createForm($formType, $object, array(
            'action' => $this->generateUrl('object_create', array(
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
     * Show object entity
     *
     * @param Object $object Object to show
     */
    public function showAction(Request $request,Object $object)
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
    * Display form to remove object entity
    *
    * @param Object $object
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
     * Delete object entity
     *
     * @param Object $object Object to delete
     *
     */
    public function deleteAction(Request $request, Object $object)
    {
        if ($request->isXmlHttpRequest()) {

            //Remove all properties of deleted object
            foreach ($object->getProperties() as $property) {
                $this->em->remove($property); 
            }

            $this->em->flush(); 

            //Remove object from tree and attach its children to its parent
            $this->em->getRepository('SLCoreBundle:Object')->removeFromTree($object);
            $this->em->clear(); 

            //Update database schema
             $this->doctrineService->removeDoctrineFiles($object);

            //Get direct children of parent Object
            $directChildren = $this->em->getRepository('SLCoreBundle:Object')->children($object->getParent(), true); 

            foreach ($directChildren as $objectChild) {
                $this->doctrineService->doctrineGenerateEntityFileByObject($objectChild);  
            }
            
            $this->doctrineService->doctrineSchemaUpdateForce();

            $arrayResponse = array(
                'isValid' => true,
                'content' => array(
                    'html' => null,
                    'js_tree' => 'delete',
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
     * Delete object Form
     *
     * @param Object $object
     *
     * @return Form $form
     */
    private function createDeleteForm(Object $object)
    {
        $formType = ($object->isDocument())?'document':'object';
        
        $form = $this->createForm($formType, $object, array(
            'action' => $this->generateUrl('object_delete', array('id' => $object->getId())),
            'method' => 'DELETE',
            'submit_label' => 'delete',
            'submit_color' => 'danger',
            'disabled_parent_field' => false,
            'object' => $object
            )
        );

        return $form;
    }
}
