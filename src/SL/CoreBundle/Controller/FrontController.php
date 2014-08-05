<?php

namespace SL\CoreBundle\Controller;

//Symfony classes
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\RegistryInterface;
use JMS\DiExtraBundle\Annotation as DI;

//Custom classes
use SL\CoreBundle\Entity\Object;
use SL\DataBundle\Entity\LogEntry;
use SL\CoreBundle\Form\FrontType;
use SL\CoreBundle\Services\DoctrineService;
use SL\CoreBundle\Services\ObjectService;
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
    private $objectService;
    private $frontService;
    private $loggableService;

    /**
     * @DI\InjectParams({
     *     "registry" = @DI\Inject("doctrine"),
     *     "doctrineService" = @DI\Inject("sl_core.doctrine"),
     *     "objectService" = @DI\Inject("sl_core.object"),
     *     "frontService" = @DI\Inject("sl_core.front"),
     *     "loggableService" = @DI\Inject("sl_core.loggable")
     * })
     */
    public function __construct(RegistryInterface $registry, DoctrineService $doctrineService, ObjectService $objectService, FrontService $frontService, LoggableService $loggableService)
    { 
        $this->em = $registry->getManager();
        $this->databaseEm = $registry->getManager('database');
        $this->doctrineService = $doctrineService;
        $this->objectService = $objectService;
        $this->frontService = $frontService;
        $this->loggableService = $loggableService;
    }

    /**
    * Display form to create entity
    *
    * @param Object $object Object type of new entity
    */
    public function newAction(Object $object)
    {
        $class = $this->doctrineService->getDataEntityClass($object->getTechnicalName());
        $entity =  new $class(); 

        $form   = $this->frontService->createCreateForm($object, $entity);

        return $this->render('SLCoreBundle:Front:save.html.twig', array(
            'object' => $object,
            'form'   => $form->createView(),
            )
        );
    }

    /**
     * Create entity
     *
     * @param Object $object Object type of new entity
     */
    public function createAction(Request $request, Object $object)
    {
        $class = $this->doctrineService->getDataEntityClass($object->getTechnicalName());
        $entity =  new $class(); 
        
        $form = $this->frontService->createCreateForm($object, $entity);
        
        $form->handleRequest($request);

        if ($request->isXmlHttpRequest()) {

            $isValid = $form->isValid();
            if ($isValid) {

                //Calculate displayName
                $displayName = $this->objectService->calculateDisplayName($entity, $object);
                $entity->setDisplayName($displayName); 

                $entity->setObjectId($object->getId()); 
               
                $this->databaseEm->persist($entity);
                $this->databaseEm->flush();

                $content = null; 

            }
            else {

                //Create a form with field error 
                $content = $this->renderView('SLCoreBundle:Front:save.html.twig', array(
                    'object' => $object,
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
     * Display form to edit entity
     *
     * @param integer $id Object type id of update entity
     * @param integer $entity_id
     */
    public function editAction($id, $entity_id)
    {
        $filters = $this->em->getFilters();
        $filters->disable('softdeleteable');

        $object = $this->em->getRepository('SLCoreBundle:Object')->fullFindById($id); 

        $entity = $this->databaseEm->getRepository('SLDataBundle:'.$object->getTechnicalName())->find($entity_id);

        $form = $this->frontService->createEditForm($object, $entity);

        $filters->enable('softdeleteable');

        return $this->render('SLCoreBundle:Front:save.html.twig', array(
            'object' => $object,
            'form'   => $form->createView(),
            )
        );
    }

    /**
     * Update entity
     *
     * @param integer $id Object type id of update entity
     * @param integer $entity_id Id of update entity
     */
    public function updateAction(Request $request, $id, $entity_id)
    {
        $filters = $this->em->getFilters();
        $filters->disable('softdeleteable');

        $object = $this->em->getRepository('SLCoreBundle:Object')->fullFindById($id); 

        $entity = $this->databaseEm->getRepository('SLDataBundle:'.$object->getTechnicalName())->find($entity_id);

        $form = $this->frontService->createEditForm($object, $entity);
        $form->handleRequest($request);

        if ($request->isXmlHttpRequest()) {

            $isValid = $form->isValid();
            if ($isValid) {

                //Calculate displayName value
                $displayName = $this->objectService->calculateDisplayName($entity, $object);
                $entity->setDisplayName($displayName); 
                $this->databaseEm->flush();

                $content = $displayName; 
            }
            else {
                 //Create a form with field error 
                $content = $this->renderView('SLCoreBundle:Front:save.html.twig', array(
                    'object' => $object,
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
     * @param integer $id  Object type id
     * @param integer $entity_id Id of entity to show
     *
     */
    public function showAction(Request $request, $id, $entity_id)
    {
        if ($request->isXmlHttpRequest()) {

            $filters = $this->em->getFilters();
            $filters->disable('softdeleteable');

            $object = $this->em->getRepository('SLCoreBundle:Object')->fullFindById($id); 

            $entity = $this->databaseEm->getRepository('SLDataBundle:'.$object->getTechnicalName())->find($entity_id);

            $path = $this->objectService->getObjectPath($object); 

            $objects = $this->objectService->getPath($object); 

            $currentVersion = $this->databaseEm->getRepository('SLDataBundle:LogEntry')->findCurrentVersion($entity);

            $filters->enable('softdeleteable');

            $response = $this->render('SLCoreBundle:Front:show.html.twig', array(
                'object' => $object, 
                'objects' => $objects,
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
    * @param integer $id  Object type id of remove entity
    * @param integer $entity_id
    */
    public function removeAction($id, $entity_id)
    {
        $filters = $this->em->getFilters();
        $filters->disable('softdeleteable');

        $object = $this->em->getRepository('SLCoreBundle:Object')->fullFindById($id); 
        $objects = $this->objectService->getPath($object); 

        $entity = $this->databaseEm->getRepository('SLDataBundle:'.$object->getTechnicalName())->find($entity_id);
  
        $form = $this->frontService->createDeleteForm($object, $entity);

        $filters->enable('softdeleteable');

        return $this->render('SLCoreBundle:Front:save.html.twig', array(
            'entity' => $entity,
            'object' => $object,
            'objects' => $objects,
            'form'   => $form->createView(),
            )
        );
    }

    /**
     * Delete entity
     *
     * @param integer $id  Object type id of remove entity
     * @param integer $entity_id Id of entity to delete
     */
    public function deleteAction(Request $request, $id, $entity_id)
    {
        $filters = $this->em->getFilters();
        $filters->disable('softdeleteable');

        $object = $this->em->getRepository('SLCoreBundle:Object')->fullFindById($id); 

        $entity = $this->databaseEm->getRepository('SLDataBundle:'.$object->getTechnicalName())->find($entity_id);

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
            $response = $this->redirect($this->generateUrl('front_end', array('object_id' => $object->getId())));
        }   

        $filters->enable('softdeleteable');

        return $response;    
    }

    /**
     * Display form to revert to a specific entity version
     *
     * @param integer $id  Object type id
     * @param integer $entity_id Id of entity to show
     *
     */
    public function editVersionAction(Request $request, $id, $entity_id)
    {
        if ($request->isXmlHttpRequest()) {

            $limit = $this->container->getParameter('version_number');

            $filters = $this->em->getFilters();
            $filters->disable('softdeleteable');

            $object = $this->em->getRepository('SLCoreBundle:Object')->fullFindById($id); 
            $objects = $this->objectService->getPath($object); 

            $entity = $this->databaseEm->getRepository('SLDataBundle:'.$object->getTechnicalName())->find($entity_id);

            //Form creation
            $logEntry = new LogEntry(); 
            $form = $this->frontService->createEditVersionForm($object, $entity, $logEntry, $limit);

            //Get all data version for $entity
            $formatedLogEntries = $this->loggableService->getFormatedLogEntries($objects, $entity, $limit); 

            $filters->enable('softdeleteable');

            $response = $this->render('SLCoreBundle:Front:version.html.twig', array(
                'objects' => $objects, 
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
     * @param integer $id  Object type id
     * @param integer $entity_id
     *
     * @return JsonResponse
     */
    public function updateVersionAction(Request $request, $id, $entity_id)
    {
        if ($request->isXmlHttpRequest()) {

            $object = $this->em->getRepository('SLCoreBundle:Object')->fullFindById($id); 
            $entity = $this->databaseEm->getRepository('SLDataBundle:'.$object->getTechnicalName())->find($entity_id);

            //Form creation
            $logEntry = new LogEntry(); 
            $form = $this->frontService->createEditVersionForm($object, $entity, $logEntry);
            $form->handleRequest($request);

            $this->databaseEm->getRepository('SLDataBundle:LogEntry')->revert($entity, $logEntry->getVersion());

            $displayName = $this->objectService->calculateDisplayName($entity, $object);
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
