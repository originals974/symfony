<?php

namespace SL\CoreBundle\Controller\EntityClass;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;

use SL\CoreBundle\Entity\EntityClass\EntityClass;
use SL\CoreBundle\Services\EntityClass\EntityClassService;
use SL\CoreBundle\Services\JSTreeService;
use SL\CoreBundle\Services\DoctrineService;
use SL\CoreBundle\Services\EntityService;

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
    private $entityService;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "entityClassService" = @DI\Inject("sl_core.entity_class"),
     *     "jstreeService" = @DI\Inject("sl_core.js_tree"),
     *     "doctrineService" = @DI\Inject("sl_core.doctrine"),
     *     "entityService" = @DI\Inject("sl_core.entity")
     * })
     */
    public function __construct(EntityManager $em, EntityClassService $entityClassService, JSTreeService $jstreeService, DoctrineService $doctrineService, EntityService $entityService)
    {
        $this->em = $em;
        $this->entityClassService = $entityClassService;
        $this->jstreeService = $jstreeService;
        $this->doctrineService = $doctrineService;
        $this->entityService = $entityService;
    }

    /**
     * Display entity class main screen
     *
     * @param Symfony\Component\HttpFoundation\Request $request
     *
     * @return Symfony\Component\HttpFoundation\Response $response 
     */
    public function indexAction(Request $request)
    {   
        if ($request->isXmlHttpRequest()) {

            $response = $this->render('SLCoreBundle:EntityClass/EntityClass:index.html.twig');
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }

        return $response; 
    }

    /**
    * Display form to create an entity class
    * associated with $parentEntityClass
    *
    * @param Symfony\Component\HttpFoundation\Request $request
    * @param SL\CoreBundle\Entity\EntityClass\EntityClass $parentEntityClass|null
    *
    * @return Symfony\Component\HttpFoundation\Response $response
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
     * Create an entity class
     * associated with $parentEntityClass
     *
     * @param Symfony\Component\HttpFoundation\Request $request
     * @param SL\CoreBundle\Entity\EntityClass\EntityClass $parentEntityClass|null
     *
     * @return Mixed $response
     */
    public function createAction(Request $request, EntityClass $parentEntityClass = null)
    {
        //Get field type of default property associated to new entity class
        //Create a default property only for root entity class
        if($parentEntityClass == null) {
            $fieldType = $this->em->getRepository('SLCoreBundle:Field\FieldType')->findOneByFormType('text');
        }
        else{
            $fieldType = null; 
        }
        $entityClass = new EntityClass($fieldType, $parentEntityClass);
        $form = $this->entityClassService->createCreateForm($entityClass);
        $form->handleRequest($request);
 
        if ($request->isXmlHttpRequest()) {

            if ($form->isValid()) {

                $this->em->persist($entityClass);
                $this->em->flush();

                //Init calculated name with default created property
                $this->entityClassService->initCalculatedName($entityClass); 
                
                $this->doctrineService->createDoctrineEntityFileAndObjectSchema($entityClass);  
                
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
                'isValid' => $form->isValid(),
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
    * Display form to edit $entityClass
    *
    * @param Symfony\Component\HttpFoundation\Request $request
    * @param SL\CoreBundle\Entity\EntityClass\EntityClass $entityClass 
    *
    * @return Symfony\Component\HttpFoundation\Response $response
    */
    public function editAction(Request $request, EntityClass $entityClass)
    {
        if ($request->isXmlHttpRequest()) {
            $form = $this->entityClassService->createEditForm($entityClass);
     
            $response = $this->render('SLCoreBundle::save.html.twig', array(
                'entity' => $entityClass,
                'form'   => $form->createView(),
                )
            );
        }
        else{
            $response = $this->redirect($this->generateUrl('back_end'));
        }

        return $response;
    }

    /**
    * Update $entityClass
    *
    * @param Symfony\Component\HttpFoundation\Request $request
    * @param SL\CoreBundle\Entity\EntityClass\EntityClass $entityClass
    *
    * @return Mixed $response
    */
    public function updateAction(Request $request, EntityClass $entityClass)
    {
        $form = $this->entityClassService->createEditForm($entityClass);
        $form->handleRequest($request);

        if ($request->isXmlHttpRequest()) {

            if ($form->isValid()) {

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
                'isValid' => $form->isValid(),
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
     * Show $entityClass and its property
     *
     * @param Symfony\Component\HttpFoundation\Request $request
     * @param SL\CoreBundle\Entity\EntityClass\EntityClass $entityClass
     *
     * @return Symfony\Component\HttpFoundation\Response $response
     *
     * @ParamConverter("entityClass", options={"repository_method" = "fullFindById"})
     */
    public function showAction(Request $request, EntityClass $entityClass)
    {
        if ($request->isXmlHttpRequest()) {

            $response = $this->render('SLCoreBundle:EntityClass/EntityClass:show.html.twig', array(
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
    * Display form to remove $entityClass
    *
    * @param Symfony\Component\HttpFoundation\Request $request
    * @param SL\CoreBundle\Entity\EntityClass\EntityClass $entityClass
    *
    * @return Symfony\Component\HttpFoundation\Response $response
    */
    public function removeAction(Request $request, EntityClass $entityClass)
    {
        if ($request->isXmlHttpRequest()) {

            //Entity class integrity control before delete
            $integrityError = $this->entityClassService->integrityControlBeforeDelete($entityClass); 
            if($integrityError === null) {
                       
                $form = $this->entityClassService->createDeleteForm($entityClass);

                $response = $this->render('SLCoreBundle::save.html.twig', array(
                    'entity' => $entityClass,
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
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }

        return $response; 
    }

    /**
     * Delete $entityClass
     *
     * @param Symfony\Component\HttpFoundation\Request $request
     * @param SL\CoreBundle\Entity\EntityClass\EntityClass $entityClass
     *
     * @return Mixed $response
     */
    public function deleteAction(Request $request, EntityClass $entityClass)
    {
        if ($request->isXmlHttpRequest()) {

            // If entities exist for entity class, entity class is soft delete
            // Entity and database table associated with entity class aren't deleted 
            $entitiesExist = $this->entityService->entitiesExist($entityClass); 
            if($entitiesExist){
               $this->doctrineService->entityDelete('SLCoreBundle:EntityClass\EntityClass', $entityClass->getId(), false);
            }
            // Otherwise entity class is hard delete
            // Entity and database table associated with entity class are deleted too
            else{
                $this->doctrineService->removeDoctrineFiles($entityClass);

                //Remove children entities and database tables of deleted entity class
                $children = $this->em->getRepository('SLCoreBundle:EntityClass\EntityClass')->children($entityClass); 
                foreach ($children as $child) {
                    $this->doctrineService->removeDoctrineFiles($child);
                }

                $this->doctrineService->doctrineSchemaUpdateForce();

                $this->doctrineService->entityDelete('SLCoreBundle:EntityClass\EntityClass', $entityClass->getId(), true);
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
    * Display form to edit $entityClass calculated name
    *
    * @param Symfony\Component\HttpFoundation\Request $request
    * @param SL\CoreBundle\Entity\EntityClass\EntityClass $entityClass
    *
    * @return Symfony\Component\HttpFoundation\Response $response
    */
    public function editCalculatedNameAction(Request $request, EntityClass $entityClass)
    {
        if ($request->isXmlHttpRequest()) {
            $form = $this->entityClassService->createEditCalculatedNameForm($entityClass);
     
            $entityClasses = $this->em->getRepository('SLCoreBundle:EntityClass\EntityClass')->getPath($entityClass); 
            $response = $this->render('SLCoreBundle:EntityClass/EntityClass:calculatedNameDesigner.html.twig', array(
                'entityClasses' => $entityClasses,
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
    * Update $entityClass calculated name
    *
    * @param Symfony\Component\HttpFoundation\Request $request
    * @param SL\CoreBundle\Entity\EntityClass\EntityClass $entityClass
    *
    * @return Mixed $response
    */
    public function updateCalculatedNameAction(Request $request, EntityClass $entityClass)
    {
        $form = $this->entityClassService->createEditCalculatedNameForm($entityClass);
        $form->handleRequest($request);

        if ($request->isXmlHttpRequest()) {

            if ($form->isValid()) {

                if($form->get('updateExistingDisplayName')->getData()) {
                    //Refresh display name of existing entity
                    $this->entityService->refreshCalculatedName($entityClass); 
                }

                $this->em->flush();
                $html = null; 
            }
            else {
                $entityClasses = $this->em->getRepository('SLCoreBundle:EntityClass\EntityClass')->getPath($entityClass); 
                $html = $this->renderView('SLCoreBundle:EntityClass/EntityClass:calculatedNameDesigner.html.twig', array(
                    'entityClasses' => $entityClasses,
                    'form'   => $form->createView(),
                    )
                );
            }
            
            $arrayResponse = array(
                'isValid' => $form->isValid(),
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
     * Update $entityClass icon
     *
     * @param Symfony\Component\HttpFoundation\Request $request
     * @param SL\CoreBundle\Entity\EntityClass\EntityClass $entityClass
     *
     * @return Mixed $response
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
