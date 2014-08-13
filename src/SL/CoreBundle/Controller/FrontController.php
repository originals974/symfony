<?php

namespace SL\CoreBundle\Controller;

//Symfony classes
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bridge\Doctrine\RegistryInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

//Custom classes
use SL\CoreBundle\Entity\EntityClass\EntityClass;
use SL\DataBundle\Entity\LogEntry;
use SL\CoreBundle\Services\DoctrineService;
use SL\CoreBundle\Services\EntityClass\EntityClassService;
use SL\CoreBundle\Services\FrontService;
use SL\CoreBundle\Services\LoggableService;

/**
 * Front controller.
 *
 */
class FrontController extends Controller
{
    private $em;
    private $databaseEm;
    private $doctrineService;
    private $entityClassService;
    private $frontService;

    /**
     * @DI\InjectParams({
     *     "registry" = @DI\Inject("doctrine"),
     *     "doctrineService" = @DI\Inject("sl_core.doctrine"),
     *     "entityClassService" = @DI\Inject("sl_core.entity_class"),
     *     "frontService" = @DI\Inject("sl_core.front"),
     * })
     */
    public function __construct(RegistryInterface $registry, DoctrineService $doctrineService, entityClassService $entityClassService, FrontService $frontService)
    { 
        $this->em = $registry->getManager();
        $this->databaseEm = $registry->getManager('database');
        $this->doctrineService = $doctrineService;
        $this->entityClassService = $entityClassService;
        $this->frontService = $frontService;
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

            $form   = $this->frontService->createCreateForm($entity);

            $response = $this->render('SLCoreBundle:Front:save.html.twig', array(
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
            
            $form = $this->frontService->createCreateForm($entity);
            $form->handleRequest($request);


            if ($form->isValid()) {

                $displayName = $this->frontService->calculateDisplayName($entity, $entityClass);
                $entity->setDisplayName($displayName); 
               
                $this->databaseEm->persist($entity);
                $this->databaseEm->flush();

                $content = null; 
            }
            else {

                //Create a form with field error 
                $content = $this->renderView('SLCoreBundle:Front:save.html.twig', array(
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
     */
    public function editAction(Request $request, EntityClass $entityClass, $entity_id)
    {
        if ($request->isXmlHttpRequest()) {

            $entity = $this->databaseEm->getRepository('SLDataBundle:'.$entityClass->getTechnicalName())->find($entity_id);

            $form = $this->frontService->createEditForm($entity);

            $response = $this->render('SLCoreBundle:Front:save.html.twig', array(
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
     */
    public function updateAction(Request $request, EntityClass $entityClass, $entity_id)
    {
        if ($request->isXmlHttpRequest()) {

            $entity = $this->databaseEm->getRepository('SLDataBundle:'.$entityClass->getTechnicalName())->find($entity_id);

            $form = $this->frontService->createEditForm($entity);
            $form->handleRequest($request);

            $isValid = $form->isValid();
            if ($isValid) {

                //Calculate displayName value
                $displayName = $this->frontService->calculateDisplayName($entity, $entityClass);
                $entity->setDisplayName($displayName); 
                $this->databaseEm->flush();

                $content = $displayName; 
            }
            else {
                 //Create a form with field error 
                $content = $this->renderView('SLCoreBundle:Front:save.html.twig', array(
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
     */
    public function showAction(Request $request,EntityClass $entityClass, $entity_id)
    {
        if ($request->isXmlHttpRequest()) {

            $entity = $this->databaseEm->getRepository('SLDataBundle:'.$entityClass->getTechnicalName())->find($entity_id);

            $path = $this->entityClassService->getEntityClassPath($entityClass); 

            $entityClasses = $this->entityClassService->getPath($entityClass); 

            $currentVersion = $this->databaseEm->getRepository('SLDataBundle:LogEntry')->findCurrentVersion($entity);

            $response = $this->render('SLCoreBundle:Front:show.html.twig', array(
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
    */
    public function removeAction(Request $request, EntityClass $entityClass, $entity_id)
    {
        if ($request->isXmlHttpRequest()) {

            $entityClasses = $this->entityClassService->getPath($entityClass); 

            $entity = $this->databaseEm->getRepository('SLDataBundle:'.$entityClass->getTechnicalName())->find($entity_id);
      
            $form = $this->frontService->createDeleteForm($entity);

            return $this->render('SLCoreBundle:Front:save.html.twig', array(
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
     * @ParamConverter("entityClass", options={"select_mode" = "all"})
     */
    public function deleteAction(Request $request, EntityClass $entityClass, $entity_id)
    {
        if ($request->isXmlHttpRequest()) {

            $entity = $this->databaseEm->getRepository('SLDataBundle:'.$entityClass->getTechnicalName())->find($entity_id);
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
            $response = $this->redirect($this->generateUrl('front_end', array('entityClass_id' => $entityClass->getId())));
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
     */
    public function editVersionAction(Request $request, EntityClass $entityClass, $entity_id)
    {
        if ($request->isXmlHttpRequest()) {

            $limit = $this->container->getParameter('version_number');
 
            $entityClasses = $this->entityClassService->getPath($entityClass); 

            $entity = $this->databaseEm->getRepository('SLDataBundle:'.$entityClass->getTechnicalName())->find($entity_id);

            $form = $this->frontService->createEditVersionForm($entity, null, $limit);

            //Get all data version for $entity
            $formatedLogEntries = $this->doctrineService->getFormatedLogEntries($entity, $limit); 

            $response = $this->render('SLCoreBundle:Front:version.html.twig', array(
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
     * @ParamConverter("entityClass", options={"select_mode" = "all"})
     */
    public function updateVersionAction(Request $request, EntityClass $entityClass, $entity_id)
    {
        if ($request->isXmlHttpRequest()) {

            $entity = $this->databaseEm->getRepository('SLDataBundle:'.$entityClass->getTechnicalName())->find($entity_id);

            //Form creation
            $form = $this->frontService->createEditVersionForm($entity);
            $form->handleRequest($request);

            $logEntry = $form->get('logEntry')->getData(); 

            $this->databaseEm->getRepository('SLDataBundle:LogEntry')->revert($entity, $logEntry->getVersion());

            $displayName = $this->frontService->calculateDisplayName($entity);
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
