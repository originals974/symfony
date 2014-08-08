<?php

namespace SL\CoreBundle\Controller;

//Symfony classes
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bridge\Doctrine\RegistryInterface;
use JMS\DiExtraBundle\Annotation as DI;

//Custom classes
use SL\CoreBundle\Entity\EntityClass;
use SL\DataBundle\Entity\LogEntry;
use SL\CoreBundle\Services\DoctrineService;
use SL\CoreBundle\Services\EntityClassService;
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
    private $loggableService;

    /**
     * @DI\InjectParams({
     *     "registry" = @DI\Inject("doctrine"),
     *     "doctrineService" = @DI\Inject("sl_core.doctrine"),
     *     "entityClassService" = @DI\Inject("sl_core.entity.class"),
     *     "frontService" = @DI\Inject("sl_core.front"),
     *     "loggableService" = @DI\Inject("sl_core.loggable")
     * })
     */
    public function __construct(RegistryInterface $registry, DoctrineService $doctrineService, entityClassService $entityClassService, FrontService $frontService, LoggableService $loggableService)
    { 
        $this->em = $registry->getManager();
        $this->databaseEm = $registry->getManager('database');
        $this->doctrineService = $doctrineService;
        $this->entityClassService = $entityClassService;
        $this->frontService = $frontService;
        $this->loggableService = $loggableService;
    }

    /**
    * Display form to create entity
    *
    * @param EntityClass $entityClass EntityClass type of new entity
    */
    public function newAction(EntityClass $entityClass)
    {
        $class = $this->doctrineService->getDataEntityClass($entityClass->getTechnicalName());
        $entity =  new $class(); 

        $form   = $this->frontService->createCreateForm($entityClass, $entity);

        return $this->render('SLCoreBundle:Front:save.html.twig', array(
            'entityClass' => $entityClass,
            'form'   => $form->createView(),
            )
        );
    }

    /**
     * Create entity
     *
     * @param EntityClass $entityClass EntityClass type of new entity
     */
    public function createAction(Request $request, EntityClass $entityClass)
    {
        $class = $this->doctrineService->getDataEntityClass($entityClass->getTechnicalName());
        $entity =  new $class(); 
        
        $form = $this->frontService->createCreateForm($entityClass, $entity);
        $form->handleRequest($request);

        if ($request->isXmlHttpRequest()) {

            if ($form->isValid()) {

                $displayName = $this->frontService->calculateDisplayName($entity, $entityClass);
                $entity->setDisplayName($displayName); 
                $entity->setEntityClassId($entityClass->getId()); 
               
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
     */
    public function editAction($id, $entity_id)
    {
        $filters = $this->em->getFilters();
        $filters->disable('softdeleteable');

        $entityClass = $this->em->getRepository('SLCoreBundle:EntityClass')->fullFindById($id); 

        $entity = $this->databaseEm->getRepository('SLDataBundle:'.$entityClass->getTechnicalName())->find($entity_id);

        $form = $this->frontService->createEditForm($entityClass, $entity);

        $filters->enable('softdeleteable');

        return $this->render('SLCoreBundle:Front:save.html.twig', array(
            'entityClass' => $entityClass,
            'form'   => $form->createView(),
            )
        );
    }

    /**
     * Update entity
     *
     * @param integer $id EntityClass type id of update entity
     * @param integer $entity_id Id of update entity
     */
    public function updateAction(Request $request, $id, $entity_id)
    {
        $filters = $this->em->getFilters();
        $filters->disable('softdeleteable');

        $entityClass = $this->em->getRepository('SLCoreBundle:EntityClass')->fullFindById($id); 

        $entity = $this->databaseEm->getRepository('SLDataBundle:'.$entityClass->getTechnicalName())->find($entity_id);

        $form = $this->frontService->createEditForm($entityClass, $entity);
        $form->handleRequest($request);

        if ($request->isXmlHttpRequest()) {

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

        $filters->enable('softdeleteable');

        return $response; 
    }

    /**
     * Show entity
     *
     * @param integer $id  EntityClass type id
     * @param integer $entity_id Id of entity to show
     *
     */
    public function showAction(Request $request, $id, $entity_id)
    {
        if ($request->isXmlHttpRequest()) {

            $filters = $this->em->getFilters();
            $filters->disable('softdeleteable');

            $entityClass = $this->em->getRepository('SLCoreBundle:EntityClass')->fullFindById($id); 

            $entity = $this->databaseEm->getRepository('SLDataBundle:'.$entityClass->getTechnicalName())->find($entity_id);

            $path = $this->entityClassService->getEntityClassPath($entityClass); 

            $entityClasses = $this->entityClassService->getPath($entityClass); 

            $currentVersion = $this->databaseEm->getRepository('SLDataBundle:LogEntry')->findCurrentVersion($entity);

            $filters->enable('softdeleteable');

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
    */
    public function removeAction($id, $entity_id)
    {
        $filters = $this->em->getFilters();
        $filters->disable('softdeleteable');

        $entityClass = $this->em->getRepository('SLCoreBundle:EntityClass')->fullFindById($id); 
        $entityClasses = $this->entityClassService->getPath($entityClass); 

        $entity = $this->databaseEm->getRepository('SLDataBundle:'.$entityClass->getTechnicalName())->find($entity_id);
  
        $form = $this->frontService->createDeleteForm($entityClass, $entity);

        $filters->enable('softdeleteable');

        return $this->render('SLCoreBundle:Front:save.html.twig', array(
            'entity' => $entity,
            'entityClass' => $entityClass,
            'entityClasses' => $entityClasses,
            'form'   => $form->createView(),
            )
        );
    }

    /**
     * Delete entity
     *
     * @param integer $id  EntityClass type id of remove entity
     * @param integer $entity_id Id of entity to delete
     */
    public function deleteAction(Request $request, $id, $entity_id)
    {
        $filters = $this->em->getFilters();
        $filters->disable('softdeleteable');

        $entityClass = $this->em->getRepository('SLCoreBundle:EntityClass')->fullFindById($id); 

        $entity = $this->databaseEm->getRepository('SLDataBundle:'.$entityClass->getTechnicalName())->find($entity_id);

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
            $response = $this->redirect($this->generateUrl('front_end', array('entityClass_id' => $entityClass->getId())));
        }   

        $filters->enable('softdeleteable');

        return $response;    
    }

    /**
     * Display form to revert to a specific entity version
     *
     * @param integer $id  EntityClass type id
     * @param integer $entity_id Id of entity to show
     *
     */
    public function editVersionAction(Request $request, $id, $entity_id)
    {
        if ($request->isXmlHttpRequest()) {

            $limit = $this->container->getParameter('version_number');

            $filters = $this->em->getFilters();
            $filters->disable('softdeleteable');

            $entityClass = $this->em->getRepository('SLCoreBundle:EntityClass')->fullFindById($id); 
            $entityClasses = $this->entityClassService->getPath($entityClass); 

            $entity = $this->databaseEm->getRepository('SLDataBundle:'.$entityClass->getTechnicalName())->find($entity_id);

            //Form creation
            $logEntry = new LogEntry(); 
            $form = $this->frontService->createEditVersionForm($entityClass, $entity, $logEntry, $limit);

            //Get all data version for $entity
            $formatedLogEntries = $this->loggableService->getFormatedLogEntries($entity, $limit); 

            $filters->enable('softdeleteable');

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
     */
    public function updateVersionAction(Request $request, $id, $entity_id)
    {
        if ($request->isXmlHttpRequest()) {

            $entityClass = $this->em->getRepository('SLCoreBundle:EntityClass')->fullFindById($id); 
            $entity = $this->databaseEm->getRepository('SLDataBundle:'.$entityClass->getTechnicalName())->find($entity_id);

            //Form creation
            $logEntry = new LogEntry(); 
            $form = $this->frontService->createEditVersionForm($entityClass, $entity, $logEntry);
            $form->handleRequest($request);

            $this->databaseEm->getRepository('SLDataBundle:LogEntry')->revert($entity, $logEntry->getVersion());

            $displayName = $this->frontService->calculateDisplayName($entity, $entityClass);
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
