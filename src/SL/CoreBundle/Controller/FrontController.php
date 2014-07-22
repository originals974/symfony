<?php

namespace SL\CoreBundle\Controller;

//Symfony classes
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;

//Custom classes
use SL\CoreBundle\Entity\Object;
use SL\CoreBundle\Form\FrontType;
use SL\CoreBundle\Services\DoctrineService;
use SL\CoreBundle\Services\ObjectService;
/**
 * Front controller.
 *
 */
class FrontController extends Controller
{
    private $registry; 
    private $em;
    private $databaseEm;
    private $doctrineService;
    private $objectService;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine"),
     *     "doctrineService" = @DI\Inject("sl_core.doctrine"),
     *     "objectService" = @DI\Inject("sl_core.object")
     * })
     */
    public function __construct(RegistryInterface $registry, DoctrineService $doctrineService, ObjectService $objectService)
    { 
        $this->em = $registry->getManager();
        $this->databaseEm = $registry->getManager('database');
        $this->doctrineService = $doctrineService;
        $this->objectService = $objectService;
    }

    /**
    * Display form to create entity
    *
    * @param Object $object Object type of new entity
    */
    public function newAction(Object $object)
    {
        $class = $this->doctrineService->getEntityClass($object->getTechnicalName());
        $entity =  new $class(); 

        $form   = $this->createCreateForm($object, $entity);

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
        $class = $this->doctrineService->getEntityClass($object->getTechnicalName());
        $entity =  new $class(); 
        
        $form = $this->createCreateForm($object, $entity);
        
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
    * Creates entity form
    *
    * @param Object $object Object type of new entity
    * @param Mixed $entity
    *
    * @return Form $form
    */
    private function createCreateForm(Object $object, $entity)
    {
        $entityClass = $this->doctrineService->getEntityClass($object->getTechnicalName());

        $form = $this->createForm(new FrontType($this->em, $entityClass), $entity, array(
            'action' => $this->generateUrl('front_create', array(
                'id' => $object->getId(),
                )
            ),
            'method' => 'POST',
            'submit_label' => 'create',
            'submit_color' => 'primary',
            'object' => $object,
            )
        );

        return $form;
    }

    /**
     * Display form to edit entity
     *
     * @param Object $object Object type of update entity
     * @param Int $entity_id
     */
    public function editAction(Object $object, $entity_id)
    {
        $entity = $this->databaseEm->getRepository('SLDataBundle:'.$object->getTechnicalName())->find($entity_id);

        $form = $this->createEditForm($object, $entity);

        return $this->render('SLCoreBundle:Front:save.html.twig', array(
            'object' => $object,
            'form'   => $form->createView(),
            )
        );
    }

    /**
     * Update entity
     *
     * @param Object $object Object type of update entity
     * @param Int $entity_id Id of update entity
     */
    public function updateAction(Request $request, Object $object, $entity_id)
    {
        $entity = $this->databaseEm->getRepository('SLDataBundle:'.$object->getTechnicalName())->find($entity_id);

        $form = $this->createEditForm($object, $entity);
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

        return $response; 
    }


    /**
    * Update entity form
    *
    * @param Object $object Object type of update entity
    * @param Mixed $entity
    *
    * @return Form $form
    */
    private function createEditForm(Object $object, $entity)
    {
        $entityClass = $this->doctrineService->getEntityClass($object->getTechnicalName());

        $form = $this->createForm(new FrontType($this->em, $entityClass), $entity, array(
            'action' => $this->generateUrl('front_update', array(
                'id' => $object->getId(),
                'entity_id' => $entity->getId(),
                )
            ),
            'method' => 'PUT',
            'submit_label' => 'update',
            'submit_color' => 'primary',
            'object' => $object,
            )
        );
        
        return $form;
    }

    /**
     * Show entity
     *
     * @param Object $object  Object type of show entity
     * @param Int $entity_id Id of entity to show
     */
    public function showAction(Request $request,Object $object, $entity_id)
    {
        if ($request->isXmlHttpRequest()) {

            $entity = $this->databaseEm->getRepository('SLDataBundle:'.$object->getTechnicalName())->find($entity_id);

            $path = $this->objectService->getObjectPath($object); 

            $objects = $this->em->getRepository('SLCoreBundle:Object')->getPath($object); 

            $response = $this->render('SLCoreBundle:Front:show.html.twig', array(
                'object' => $object, 
                'objects' => $objects,
                'entity' => $entity, 
                'path' => $path,
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
    * @param Object $object  Object type of remove entity
    * @param Int $entity_id
    */
    public function removeAction(Object $object, $entity_id)
    {
        $entity = $this->databaseEm->getRepository('SLDataBundle:'.$object->getTechnicalName())->find($entity_id);
  
        $form = $this->createDeleteForm($object, $entity);

        return $this->render('SLCoreBundle::save.html.twig', array(
            'entity' => $object,
            'form'   => $form->createView(),
            )
        );
    }

    /**
     * Delete entity
     *
     * @param Object $object  Object type of remove entity
     * @param Int $entity_id Id of entity to delete
     */
    public function deleteAction(Request $request, Object $object, $entity_id)
    {
        $entity = $this->databaseEm->getRepository('SLDataBundle:'.$object->getTechnicalName())->find($entity_id);

        if ($request->isXmlHttpRequest()) {

            $this->databaseEm->remove($entity);
            $this->databaseEm->flush();

            //Create the Json Response array
            $data = array(  
                'isValid' => true,
                'content' => null,
            );

            $response = new JsonResponse($data);
        }
        else {
            $response = $this->redirect($this->generateUrl('front_end', array('object_id' => $object->getId())));
        }   

        return $response;    
    }

    /**
     * Delete entity form
     *
     * @param Object $object  Object type of remove entity
     * @param Mixed $entity
     *
     * @return Form $form Delete form
     */
    private function createDeleteForm(Object $object, $entity)
    {
        $entityClass = $this->doctrineService->getEntityClass($object->getTechnicalName());

        $form = $this->createForm(new FrontType($this->em, $entityClass), $entity, array(
            'action' => $this->generateUrl('front_delete', array(
                'id' => $object->getId(),
                'entity_id' => $entity->getId(),
                )
            ),
            'method' => 'DELETE',
            'submit_label' => 'delete',
            'submit_color' => 'danger',
            'object' => $object,
            )
        );

        return $form;
    }
}
