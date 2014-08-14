<?php

namespace SL\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bridge\Doctrine\RegistryInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use SL\MasterBundle\Entity\AbstractEntity; 

use SL\CoreBundle\Entity\EntityClass\EntityClass;
use SL\CoreBundle\Services\DoctrineService;
use SL\CoreBundle\Services\EntityClass\EntityClassService;
use SL\CoreBundle\Services\EntityService;

/**
 * Entity controller.
 *
 */
class EntityController extends Controller
{
    private $databaseEm;
    private $doctrineService;
    private $entityClassService;
    private $entityService;

    /**
     * @DI\InjectParams({
     *     "registry" = @DI\Inject("doctrine"),
     *     "doctrineService" = @DI\Inject("sl_core.doctrine"),
     *     "entityClassService" = @DI\Inject("sl_core.entity_class"),
     *     "entityService" = @DI\Inject("sl_core.entity"),
     * })
     */
    public function __construct(RegistryInterface $registry, DoctrineService $doctrineService, EntityClassService $entityClassService, EntityService $entityService)
    { 
        $this->databaseEm = $registry->getManager('database');
        $this->doctrineService = $doctrineService;
        $this->entityClassService = $entityClassService;
        $this->entityService = $entityService;
    }

    /**
    * Display form to create entity
    *
    * @param EntityClass\EntityClass $entityClass EntityClass type of new entity
    */
    public function newAction(Request $request, EntityClass $entityClass)
    {
        if ($request->isXmlHttpRequest()) {

            $class = $this->doctrineService->getDataEntityNamespace($entityClass->getTechnicalName());
            $entity =  new $class($entityClass->getId()); 

            $form   = $this->entityService->createCreateForm($entity);

            $response = $this->render('SLCoreBundle:Entity:save.html.twig', array(
                'entityClass' => $entityClass,
                'form'   => $form->createView(),
                )
            );
        }
        else {
            $response = $this->redirect($this->generateUrl('front_end'));
        }

        return $response; 
    }

    /**
     * Create entity
     *
     * @param EntityClass\EntityClass $entityClass EntityClass type of new entity
     */
    public function createAction(Request $request, EntityClass $entityClass)
    {
        if ($request->isXmlHttpRequest()) {

            $class = $this->doctrineService->getDataEntityNamespace($entityClass->getTechnicalName());
            $entity =  new $class($entityClass->getId()); 
            
            $form = $this->entityService->createCreateForm($entity);
            $form->handleRequest($request);


            if ($form->isValid()) {

                $displayName = $this->entityService->calculateDisplayName($entity, $entityClass);
                $entity->setDisplayName($displayName); 
               
                $this->databaseEm->persist($entity);
                $this->databaseEm->flush();

                $content = null; 
            }
            else {

                //Create a form with field error 
                $content = $this->renderView('SLCoreBundle:Entity:save.html.twig', array(
                    'entityClass' => $entityClass,
                    'form'   => $form->createView(),
                    )
                ); 
            }

            //Create the Json Response array
            $data = array(  
                'isValid' => $form->isValid(),
                'content' => $content,
            );

            $response = new JsonResponse($data);
        }
        else {
            $response = $this->redirect($this->generateUrl('front_end'));
        }

        return $response; 
    }

    /**
     * Display form to edit entity
     *
     * @param integer $id EntityClass type id of update entity
     * @param integer $entity_id
     *
     * @ParamConverter("entityClass", options={"select_mode" = "all"})
     * @ParamConverter("entity", options={"select_mode" = "all"})
     */
    public function editAction(Request $request, EntityClass $entityClass, AbstractEntity $entity, $class_namespace)
    {
        if ($request->isXmlHttpRequest()) {

            $form = $this->entityService->createEditForm($entity);

            $response = $this->render('SLCoreBundle:Entity:save.html.twig', array(
                'entityClass' => $entityClass,
                'form'   => $form->createView(),
                )
            );
        }
        else {
            $response = $this->redirect($this->generateUrl('front_end'));
        }

        return $response; 
    }

    /**
     * Update entity
     *
     * @param integer $id EntityClass type id of update entity
     * @param integer $entity_id Id of update entity
     *
     * @ParamConverter("entityClass", options={"select_mode" = "all"})
     * @ParamConverter("entity", options={"select_mode" = "all"})
     */
    public function updateAction(Request $request, EntityClass $entityClass, AbstractEntity $entity, $class_namespace)
    {
        if ($request->isXmlHttpRequest()) {

            $form = $this->entityService->createEditForm($entity);
            $form->handleRequest($request);

            $isValid = $form->isValid();
            if ($isValid) {

                //Calculate displayName value
                $displayName = $this->entityService->calculateDisplayName($entity, $entityClass);
                $entity->setDisplayName($displayName); 
                $this->databaseEm->flush();

                $content = $displayName; 
            }
            else {
                 //Create a form with field error 
                $content = $this->renderView('SLCoreBundle:Entity:save.html.twig', array(
                    'entityClass' => $entityClass,
                    'form'   => $form->createView(),
                    )
                ); 
            }

            //Create the Json Response array
            $data = array(  
                'isValid' => $isValid,
                'content' => $content,
            );

            $response = new JsonResponse($data);
        }

        else {

            $response = $this->redirect($this->generateUrl('front_end'));
        }

        return $response; 
    }

    /**
     * Show entity
     *
     * @param integer $id  EntityClass type id
     * @param integer $entity_id Id of entity to show
     *
     * @ParamConverter("entityClass", options={"select_mode" = "all"})
     * @ParamConverter("entity", options={"select_mode" = "all"})
     */
    public function showAction(Request $request,EntityClass $entityClass, AbstractEntity $entity, $class_namespace)
    {
        if ($request->isXmlHttpRequest()) {

            $path = $this->entityClassService->getEntityClassPath($entityClass); 

            $entityClasses = $this->entityClassService->getPath($entityClass); 

            $currentVersion = $this->databaseEm->getRepository('SLDataBundle:LogEntry')->findCurrentVersion($entity);

            $response = $this->render('SLCoreBundle:Entity:show.html.twig', array(
                'entityClass' => $entityClass, 
                'entityClasses' => $entityClasses,
                'entity' => $entity, 
                'path' => $path,
                'currentVersion' => array_shift($currentVersion),
                )
            );
        }
        else {
            $response = $this->redirect($this->generateUrl('front_end'));
        }

        return $response; 
    }

    /**
    * Display form to remove entity
    *
    * @param integer $id  EntityClass type id of remove entity
    * @param integer $entity_id
    *
    * @ParamConverter("entityClass", options={"select_mode" = "all"})
    * @ParamConverter("entity", options={"select_mode" = "all"})
    */
    public function removeAction(Request $request, EntityClass $entityClass, AbstractEntity $entity, $class_namespace)
    {
        if ($request->isXmlHttpRequest()) {

            $entityClasses = $this->entityClassService->getPath($entityClass); 

            $form = $this->entityService->createDeleteForm($entity);

            return $this->render('SLCoreBundle:Entity:save.html.twig', array(
                'entity' => $entity,
                'entityClass' => $entityClass,
                'entityClasses' => $entityClasses,
                'form'   => $form->createView(),
                )
            );
        }
        else {
            $response = $this->redirect($this->generateUrl('front_end'));
        }

        return $response;
    }

    /**
     * Delete entity
     *
     * @param integer $id  EntityClass type id of remove entity
     * @param integer $entity_id Id of entity to delete
     *
     * @ParamConverter("entity", options={"select_mode" = "all"})
     */
    public function deleteAction(Request $request, AbstractEntity $entity, $class_namespace)
    {
        if ($request->isXmlHttpRequest()) {

            $this->databaseEm->remove($entity);
            $this->databaseEm->flush();

            $data = array(  
                'isValid' => true,
                'content' => null,
                'mode' => 'delete',
            );

            $response = new JsonResponse($data);
        }
        else {
            $response = $this->redirect($this->generateUrl('front_end'));
        }   

        return $response;    
    }

    /**
     * Display form to revert to a specific entity version
     *
     * @param integer $id  EntityClass type id
     * @param integer $entity_id Id of entity to show
     *
     * @ParamConverter("entityClass", options={"select_mode" = "all"})
     * @ParamConverter("entity", options={"select_mode" = "all"})
     */
    public function editVersionAction(Request $request, EntityClass $entityClass, AbstractEntity $entity, $class_namespace)
    {
        if ($request->isXmlHttpRequest()) {

            $limit = $this->container->getParameter('version_number');
 
            $entityClasses = $this->entityClassService->getPath($entityClass); 

            $form = $this->entityService->createEditVersionForm($entity, null, $limit);

            //Get all data version for $entity
            $formatedLogEntries = $this->doctrineService->getFormatedLogEntries($entity, $limit); 

            $response = $this->render('SLCoreBundle:Entity:version.html.twig', array(
                'entityClasses' => $entityClasses, 
                'entity' => $entity, 
                'formatedLogEntries' => $formatedLogEntries,
                'form'   => $form->createView(),
                )
            ); 
        }
        else {
            $response = $this->redirect($this->generateUrl('front_end'));
        }

        return $response; 
    }

     /**
     * Revert to a specific version for $entity_id
     *
     * @param integer $id  EntityClass type id
     * @param integer $entity_id
     *
     * @return JsonResponse
     *
     * @ParamConverter("entity", options={"select_mode" = "all"})
     */
    public function updateVersionAction(Request $request, AbstractEntity $entity, $class_namespace)
    {
        if ($request->isXmlHttpRequest()) {

            //Form creation
            $form = $this->entityService->createEditVersionForm($entity);
            $form->handleRequest($request);

            $logEntry = $form->get('logEntry')->getData(); 

            $this->databaseEm->getRepository('SLDataBundle:LogEntry')->revert($entity, $logEntry->getVersion());

            $displayName = $this->entityService->calculateDisplayName($entity);
            $entity->setDisplayName($displayName); 
            $this->databaseEm->flush();

            $data = array(  
                'isValid' => true,
                'content' => $displayName,
                'mode' => 'revert',
            );

            $response = new JsonResponse($data);
            
        }
        else {
            $response = $this->redirect($this->generateUrl('front_end'));
        }

        return $response; 
    }
}
