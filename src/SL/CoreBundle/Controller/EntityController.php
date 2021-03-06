<?php

namespace SL\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\EntityManager;
use \DateTime;

use SL\CoreBundle\Entity\MappedSuperclass\DataAbstractEntity; 
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
    private $em;
    private $doctrineService;
    private $entityClassService;
    private $entityService;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "doctrineService" = @DI\Inject("sl_core.doctrine"),
     *     "entityClassService" = @DI\Inject("sl_core.entity_class"),
     *     "entityService" = @DI\Inject("sl_core.entity"),
     * })
     */
    public function __construct(EntityManager $em, DoctrineService $doctrineService, EntityClassService $entityClassService, EntityService $entityService)
    { 
        $this->em = $em;
        $this->doctrineService = $doctrineService;
        $this->entityClassService = $entityClassService;
        $this->entityService = $entityService;
    }

    /**
    * Display form to create an entity
    * having $entityClass for model
    *
    * @param Request $request
    * @param EntityClass $entityClass
    *
    * @return Response $response
    */
    public function newAction(Request $request, EntityClass $entityClass)
    {
        if ($request->isXmlHttpRequest()) {

            $class = $this->doctrineService->getEntityNamespace($entityClass->getTechnicalName());
            $entity = new $class($entityClass); 

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
     * Create an entity
     * having $entityClass for model
     *
     * @param Request $request
     * @param EntityClass $entityClass
     *
     * @return Mixed $response
     */
    public function createAction(Request $request, EntityClass $entityClass)
    {
        if ($request->isXmlHttpRequest()) {

            $class = $this->doctrineService->getEntityNamespace($entityClass->getTechnicalName());
            $entity =  new $class($entityClass); 
            
            $form = $this->entityService->createCreateForm($entity);
            $form->handleRequest($request); 

            if ($form->isValid()) { 

                $displayName = $this->entityService->calculateDisplayName($entity);
                $entity->setDisplayName($displayName); 

                $this->em->persist($entity);

                //Save files properties
                //$this->doctrineService->callUploadableManager($entityClass, $entity); 
                
                $this->em->flush();

                $content = null; 

                //Reset ES index metadada for EntityClass on first instance creation
                $entities = $this->em->getRepository('SLCoreBundle:Generated\\'.$entityClass->getTechnicalName())
                                     ->findAll();

                if(count($entities) == 1){
                    $index = 'slcore';
                    $type = $entityClass->getTechnicalName(); 
                    $indexManager = $this->get('fos_elastica.index_manager');
                    $providerRegistry = $this->get('fos_elastica.provider_registry');
                    $resetter = $this->get('fos_elastica.resetter');

                    $resetter->resetIndexType($index, $type);
                    $provider = $providerRegistry->getProvider($index, $type);
                    $provider->populate();
                    $indexManager->getIndex($index)->refresh();
                }
            }
            else {

                $content = $this->renderView('SLCoreBundle:Entity:save.html.twig', array(
                    'entityClass' => $entityClass,
                    'form'   => $form->createView(),
                    )
                ); 
            }

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
    * Display form to edit $entity
    * having $entityClass for model
    * add $class_namespace for class
    *
    * @param Request $request
    * @param EntityClass $entityClass 
    * @param DataAbstractEntity $entity 
    * @param string $class_namespace 
    *
    * @return Response $response
    *
    * @ParamConverter("entityClass", options={"select_mode" = "all"})
    * @ParamConverter("entity", options={"select_mode" = "all"})
    */
    public function editAction(Request $request, EntityClass $entityClass, DataAbstractEntity $entity, $class_namespace)
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
    * Update $entity
    * having $entityClass for model
    * add $class_namespace for class
    *
    * @param Request $request
    * @param EntityClass $entityClass 
    * @param DataAbstractEntity $entity 
    * @param string $class_namespace 
    *
    * @return Mixed $response
    *
    * @ParamConverter("entityClass", options={"select_mode" = "all"})
    * @ParamConverter("entity", options={"select_mode" = "all"})
    */
    public function updateAction(Request $request, EntityClass $entityClass, DataAbstractEntity $entity, $class_namespace)
    {
        if ($request->isXmlHttpRequest()) {

            $form = $this->entityService->createEditForm($entity);
            $form->handleRequest($request);

            if ($form->isValid()) {

                $displayName = $this->entityService->calculateDisplayName($entity, $entityClass);
                $entity->setDisplayName($displayName); 
                
                if($entity->getEntityClass()->isDocument()){
                    $entity->getDocument()->setUpdatedAt(new DateTime()); 
                }

                //Save files properties
                //$this->doctrineService->callUploadableManager($entityClass, $entity);

                $this->em->flush();

                $content = $displayName; 
            }
            else {
                $content = $this->renderView('SLCoreBundle:Entity:save.html.twig', array(
                    'entityClass' => $entityClass,
                    'form'   => $form->createView(),
                    )
                ); 
            }

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
    * Show $entity
    * having $entityClass for model
    * add $class_namespace for class
    *
    * @param Request $request
    * @param EntityClass $entityClass 
    * @param DataAbstractEntity $entity 
    * @param string $class_namespace 
    *
    * @return Response $response
    *
    * @ParamConverter("entityClass", options={"select_mode" = "all"})
    * @ParamConverter("entity", options={"select_mode" = "all"})
    */
    public function showAction(Request $request,EntityClass $entityClass, DataAbstractEntity $entity, $class_namespace)
    {
        if ($request->isXmlHttpRequest()) {

            $path = $this->entityClassService->getEntityClassPath($entityClass); 

            $entityClasses = $this->entityClassService->getPath($entityClass); 

            $currentVersion = $this->em->getRepository('SLCoreBundle:LogEntry')->findCurrentVersion($entity);

            $metadata = $this->renderView('SLCoreBundle:Entity:show.html.twig', array(
                'entityClass' => $entityClass, 
                'entityClasses' => $entityClasses,
                'entity' => $entity, 
                'path' => $path,
                'currentVersion' => array_shift($currentVersion),
                )
            );

            $data = array(  
                'metadata' => $metadata,
                'document_name' => ($entity->getDocument() != null)?$entity->getDocument()->path:'default.pdf',
                'is_document' => $entity->getEntityClass()->isDocument(),
            );

            $response = new JsonResponse($data);
        }
        else {
            $response = $this->redirect($this->generateUrl('front_end'));
        }

        return $response; 
    }

    /**
    * Display form to remove $entity
    * having $entityClass for model
    * add $class_namespace for class
    *
    * @param Request $request
    * @param EntityClass $entityClass 
    * @param DataAbstractEntity $entity 
    * @param string $class_namespace 
    *
    * @return Response $response
    *
    * @ParamConverter("entityClass", options={"select_mode" = "all"})
    * @ParamConverter("entity", options={"select_mode" = "all"})
    */
    public function removeAction(Request $request, EntityClass $entityClass, DataAbstractEntity $entity, $class_namespace)
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
    * Delete $entity
    * having $class_namespace for class
    *
    * @param Request $request
    * @param DataAbstractEntity $entity 
    * @param string $class_namespace 
    *
    * @return Mixed $response
    *
    * @ParamConverter("entity", options={"select_mode" = "all"})
    */
    public function deleteAction(Request $request, DataAbstractEntity $entity, $class_namespace)
    {
        if ($request->isXmlHttpRequest()) {

            $this->entityService->detachEntity($entity); 

            $this->em->remove($entity);
            $this->em->flush();

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
    * Display form to edit $entity version
    * having $entityClass for model
    * add $class_namespace for class
    *
    * @param Request $request
    * @param EntityClass $entityClass 
    * @param DataAbstractEntity $entity 
    * @param string $class_namespace 
    *
    * @return Response $response
    *
    * @ParamConverter("entityClass", options={"select_mode" = "all"})
    * @ParamConverter("entity", options={"select_mode" = "all"})
    */
    public function editVersionAction(Request $request, EntityClass $entityClass, DataAbstractEntity $entity, $class_namespace)
    {
        if ($request->isXmlHttpRequest()) {
 
            $entityClasses = $this->entityClassService->getPath($entityClass); 

            $form = $this->entityService->createEditVersionForm($entity);

            //Get all data version for $entity
            $formatedLogEntries = $this->doctrineService->getFormatedLogEntries($entity); 

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
    * Revert $entity to selected version
    * having $class_namespace for class
    *
    * @param Request $request
    * @param DataAbstractEntity $entity 
    * @param string $class_namespace 
    *
    * @return Mixed $response
    *
    * @ParamConverter("entity", options={"select_mode" = "all"})
    */
    public function updateVersionAction(Request $request, DataAbstractEntity $entity, $class_namespace)
    {
        if ($request->isXmlHttpRequest()) {

            $form = $this->entityService->createEditVersionForm($entity);
            $form->handleRequest($request);

            $logEntry = $form->get('logEntry')->getData(); 
            $this->em->getRepository('SLCoreBundle:LogEntry')->revert($entity, $logEntry->getVersion());

            $displayName = $this->entityService->calculateDisplayName($entity);
            $entity->setDisplayName($displayName); 
            
            $this->em->flush();

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
