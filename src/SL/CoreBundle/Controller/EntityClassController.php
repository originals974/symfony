<?php

namespace SL\CoreBundle\Controller;

//Symfony classes
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

//Custom classes
use SL\CoreBundle\Entity\EntityClass;
use SL\CoreBundle\Services\EntityClassService;
use SL\CoreBundle\Services\JSTreeService;
use SL\CoreBundle\Services\DoctrineService;
use SL\CoreBundle\Services\FrontService;

/**
 * EntityClass controller
 *
 */
class EntityClassController extends Controller
{
    private $em;
    private $entityClassService;
    private $jstreeService;
    private $doctrineService;
    private $frontService;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "entityClassService" = @DI\Inject("sl_core.entity.class"),
     *     "jstreeService" = @DI\Inject("sl_core.js_tree"),
     *     "doctrineService" = @DI\Inject("sl_core.doctrine"),
     *     "frontService" = @DI\Inject("sl_core.front")
     * })
     */
    public function __construct(EntityManager $em, EntityClassService $entityClassService, JSTreeService $jstreeService, DoctrineService $doctrineService, FrontService $frontService)
    {
        $this->em = $em;
        $this->entityClassService = $entityClassService;
        $this->jstreeService = $jstreeService;
        $this->doctrineService = $doctrineService;
        $this->frontService = $frontService;
    }

    /**
     * Display create screen
     */
    public function indexAction(Request $request)
    {   
        if ($request->isXmlHttpRequest()) {

            $response = $this->render('SLCoreBundle:EntityClass:index.html.twig');
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }

        return $response; 
    }

    /**
    * Display form to create 
    *
    * @param EntityClass $parentEntityClass
    */
    public function newAction(Request $request, EntityClass $parentEntityClass = null)
    {
        if ($request->isXmlHttpRequest()) {

            $entityClass = new EntityClass(null, $parentEntityClass);
 
            $form = $this->entityClassService->createCreateForm($entityClass);

            $response = $this->render('SLCoreBundle::save.html.twig', array(
                'entity' => $entityClass,
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
     * Create entityClass entity
     *
     * @param EntityClass $parentEntityClass Parent entityClass of new entityClass
     */
    public function createAction(Request $request, EntityClass $parentEntityClass = null)
    {
        //Get text fieldtype if necessary
        if($parentEntityClass == null) {
            $fieldType = $this->em->getRepository('SLCoreBundle:FieldType')->findOneByFormType('text');
        }
        else{
            $fieldType = null; 
        }
            
        $entityClass = new EntityClass($fieldType, $parentEntityClass);

        $form = $this->entityClassService->createCreateForm($entityClass);

        $form->handleRequest($request);
 
        if ($request->isXmlHttpRequest()) {
            
            $isValid = $form->isValid();
            if ($isValid) {

                $this->em->persist($entityClass);
                $this->em->flush();

                $this->entityClassService->initCalculatedName($entityClass); 

                //Update database schema
                $this->doctrineService->doctrineGenerateEntityFileByEntityClass($entityClass);  
                $this->doctrineService->doctrineSchemaUpdateForce();

                $html = null; 
                $jsTree = $this->jstreeService->createNewEntityClassNode($entityClass);
            
            }
            else {
                $jsTree = null; 
                $html = $this-->renderView('SLCoreBundle::save.html.twig', array(
                    'entity' => $entityClass,
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
    * Display form to edit entityClass entity
    *
    * @param EntityClass $entityClass
    */
    public function editAction(EntityClass $entityClass)
    {
        $form = $this->entityClassService->createEditForm($entityClass);
 
        return $this->render('SLCoreBundle::save.html.twig', array(
            'entity' => $entityClass,
            'form'   => $form->createView(),
            )
        );
    }

    /**
    * Update entityClass entity
    *
    * @param EntityClass $entityClass EntityClass to update
    */
    public function updateAction(Request $request, EntityClass $entityClass)
    {
        $form = $this->entityClassService->createEditForm($entityClass);
        $form->handleRequest($request);

        if ($request->isXmlHttpRequest()) {

            $isValid = $form->isValid();
            if ($isValid) {

                $this->em->flush();
                
                $jsTree = $entityClass->getDisplayName();
                $html = null; 
                
            }
            else {
                $jsTree = null;
                $html = $this->renderView('SLCoreBundle::save.html.twig', array(
                    'entity' => $entityClass,
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
     * Show entityClass entity
     *
     * @param EntityClass $entityClass EntityClass to show
     *
     * @ParamConverter("entityClass", options={"repository_method" = "fullFindById"})
     */
    public function showAction(Request $request,EntityClass $entityClass)
    {
        if ($request->isXmlHttpRequest()) {

            $response = $this->render('SLCoreBundle:EntityClass:show.html.twig', array(
                'entityClass' => $entityClass, 
                )
            );
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }

        return $response; 
    }

    /**
    * Display form to remove entityClass entity
    *
    * @param EntityClass $entityClass
    */
    public function removeAction(EntityClass $entityClass)
    {
        //EntityClass integrity control before delete
        $integrityError = $this->entityClassService->integrityControlBeforeDelete($entityClass); 
        if($integrityError == null) {
                   
            $form = $this->entityClassService->createDeleteForm($entityClass);

            $response = $this->render('SLCoreBundle::save.html.twig', array(
                'entity' => $entityClass,
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
     * Delete entityClass entity
     *
     * @param EntityClass $entityClass EntityClass to delete
     *
     */
    public function deleteAction(Request $request, EntityClass $entityClass)
    {
        if ($request->isXmlHttpRequest()) {

            $entitiesExist = $this->frontService->entitiesExist($entityClass); 

            if($entitiesExist){
                $this->em->remove($entityClass);
                $this->em->flush(); 
            }
            else{
                $this->doctrineService->removeDoctrineFiles($entityClass);

                $children = $this->em->getRepository('SLCoreBundle:EntityClass')->children($entityClass); 

                foreach ($children as $child) {
                    $this->doctrineService->removeDoctrineFiles($child);
                }

                $this->doctrineService->doctrineSchemaUpdateForce();
                $this->doctrineService->entityDelete('SLCoreBundle:EntityClass', $entityClass->getId(), true);
            }

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
    * Display form to edit calculated name
    *
    * @param EntityClass $entityClass
    */
    public function editCalculatedNameAction(EntityClass $entityClass)
    {
        $form = $this->entityClassService->createEditCalculatedNameForm($entityClass);
 
        $entityClasses = $this->em->getRepository('SLCoreBundle:EntityClass')->getPath($entityClass); 

        return $this->render('SLCoreBundle:EntityClass:entityClassNameDesigner.html.twig', array(
            'entityClasses' => $entityClasses,
            'form'   => $form->createView(),
            )
        );
    }

    /**
    * Update entityClass entity
    *
    * @param EntityClass $entityClass EntityClass to update
    */
    public function updateCalculatedNameAction(Request $request, EntityClass $entityClass)
    {
        $form = $this->entityClassService->createEditCalculatedNameForm($entityClass);

        $form->handleRequest($request);

        if ($request->isXmlHttpRequest()) {

            $isValid = $form->isValid();
            if ($isValid) {

                if($form->get('updateExistingName')->getData()) {
                    //Refresh display name of existing data
                    $this->entityClassService->refreshCalculatedName($entityClass); 
                }

                $this->em->flush();

                $html = null; 
            }
            else {
                $entityClasses = $this->em->getRepository('SLCoreBundle:EntityClass')->getPath($entityClass); 
                $html = $this->renderView('SLCoreBundle:EntityClass:entityClassNameDesigner.html.twig', array(
                    'entityClasses' => $entityClasses,
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
     * Update entityClass icon
     *
     * @param EntityClass $entityClass EntityClass to update
     */
    public function updateIconAction(Request $request, EntityClass $entityClass)
    {
        if ($request->isXmlHttpRequest()) {

            $icon = $request->request->get('icon'); 

            if($icon != $entityClass->getIcon()) {
                $entityClass->setIcon($icon); 
                $this->em->flush();
            }

            $response = new JsonResponse(null);
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }   

        return $response;    
    }
}
