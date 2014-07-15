<?php
//TO COMPLETE
namespace SL\CoreBundle\Controller;

//Symfony classes
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use JMS\DiExtraBundle\Annotation as DI;

//Custom classes
use SL\CoreBundle\Entity\Object;
use SL\CoreBundle\Form\FrontType;

/**
 * Front controller.
 *
 */
class FrontController extends Controller
{
    private $em;
    private $doctrineService;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "doctrineService" = @DI\Inject("sl_core.doctrine"),
     *     "objectService" = @DI\Inject("sl_core.object")
     * })
     */
    public function __construct($em, $doctrineService, $objectService)
    {
        $this->em = $em;
        $this->doctrineService = $doctrineService;
        $this->objectService = $objectService;
    }


    /**
     * Lists all entities
     */
	/*public function indexAction(Object $object)
    {
        //Entity managers initialisation
        $databaseEm = $this->getDoctrine()->getManager('database');

        //Get properties of the object
        $properties = $this->em->getRepository('SLCoreBundle:Property')->findBy(
            array(
                'object' => $object,
            )
        );

        $entities = $databaseEm->getRepository('SLDataBundle:'.$object->getTechnicalName())->findAll();

        //View creation
        return $this->render('SLCoreBundle:Front:index.html.twig', array(
        	'object' => $object,
            'properties' => $properties,
            'entities' => $entities,
        ));
    }*/

    /**
    * Display form to create entity
    *
    * @param Object $object Object type to create
    *
    */
    public function newAction(Object $object)
    {
        $class = $this->doctrineService->getEntityClass($object->getTechnicalName());
        $entity =  new $class(); 

        $form   = $this->createCreateForm($object, $entity);

        return $this->render('SLCoreBundle::save.html.twig', array(
            'object' => $object,
            'form'   => $form->createView(),
            )
        );
    }

    /**
     * Creates a new entity.
     *
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

                //Calculate displayName value
                $displayName = $this->objectService->calculateDisplayName($entity, $object);
                
                $entity->setDisplayName($displayName); 
                $entity->setObjectId($object->getId()); 
                
                //Save entity in database
                $DatabaseEm = $this->getDoctrine()->getManager('database');
                $DatabaseEm->persist($entity);
                $DatabaseEm->flush();

                $html = null; 

            }
            else {

                //Create a form with field error 
                $html = $this->renderView('SLCoreBundle::save.html.twig', array(
                    'object' => $object,
                    'form'   => $form->createView(),
                    )
                ); 
            }

            //Create the Json Response array
            $data = array(  
                'mode' => 'create',
                'html' => $html,
                'isValid' => $isValid,
            );

            $response = new JsonResponse($data);
        }
        else {
            $response = $this->redirect($this->generateUrl('front_end'));
        }

        return $response; 
    }


    /**
    * Creates a form to create an entity.
    *
    * @param Object $object The object definition
    * @param Mixed $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Object $object, $entity)
    {
        $entityClass = $this->doctrineService->getEntityClass($object->getTechnicalName());

        $form = $this->createForm(new FrontType($this->em, $object, $entityClass), $entity, array(
            'action' => $this->generateUrl('front_create', array('id' => $object->getId())),
            'method' => 'POST',
            )
        );
  
        $form->add('submit', 'submit', array(
            'label' => 'create',
            'attr' => array('class'=>'btn btn-primary btn-sm'),
            )
        );

        return $form;
    }

    /**
     * Show an entity
     *
     * @param Object $object The object definition
     * @param Mixed $entity The entity
     *
     * @ParamConverter("object", options={"repository_method" = "findFullById"})
     */
    public function showAction(Request $request,Object $object, $entity)
    {
        if ($request->isXmlHttpRequest()) {

            $response = $this->render('SLCoreBundle:Front:show.html.twig', array(
                'object' => $object,
                'entity' => $entity, 
                )
            );
        }
        else {
            $response = $this->redirect($this->generateUrl('front_end'));
        }

        return $response; 
    }


    /**
     * Displays a form to edit an existing entity.
     *
     * @ParamConverter("object", options={"mapping": {"object_id": "id"}})
     */
    /*public function editAction(Object $object, $entity_id)
    {
        //Variables initialisation
        $databaseEm = $this->getDoctrine()->getManager('database');
        
        //Get entity to modify
        $entity = $databaseEm->getRepository('SLDataBundle:'.$object->getTechnicalName())->find($entity_id);

        //Form creation
        $form = $this->createEditForm($object, $entity);

        //View creation
        return $this->render('SLCoreBundle:Front:save.html.twig', array(
            'action' => 'edit',
            'form'   => $form->createView(),
            )
        );
    }*/

    /**
     * Edits an existing entity.
     *
     * @ParamConverter("object", options={"mapping": {"object_id": "id"}})
     */
    /*public function updateAction(Request $request, Object $object, $entity_id)
    {
        //Variables initialisation
        $databaseEm = $this->getDoctrine()->getManager('database');
        
        //Get entity to modify
        $entity = $databaseEm->getRepository('SLDataBundle:'.$object->getTechnicalName())->find($entity_id);

        //Form creation  
        $form = $this->createEditForm($object, $entity);

        //Associate return form data with entity
        $form->handleRequest($request);

        if ($request->isXmlHttpRequest()) {

            //Form validation and Ajax response construction
            $isValid = $form->isValid();
            if ($isValid) {

                //Save entity in database
                $entity = $this->setDisplayName($entity);
                $databaseEm->flush();

                //Get properties of the object
                $em = $this->getDoctrine()->getManager();
                $properties = $em->getRepository('SLCoreBundle:Property')->findBy(array(
                    'object' => $object,
                    )
                );

                //Create a html row for entity table 
                $html = $this->renderView('SLCoreBundle:Front:entityTableRow.html.twig', array(
                    'object' => $object,
                    'properties' => $properties,
                    'entity' => $entity,
                    )
                );
            }
            else {
                //Create a form with field error 
                $html = $this->renderView('SLCoreBundle:Front:save.html.twig', array(
                    'action' => 'update',
                    'form'   => $form->createView(),
                    )
                );
            }

            //Create the Json Response array
            $data = array(
                'formAction' => 'update',  
                'html' => $html,
                'isValid' => $isValid,
                'entityType' => $object->getTechnicalName(),
                'entityId' => $entity->getId(),
                );

            $response = new JsonResponse($data);
        }

        else {
            //Redirect to index page
            $response = $this->redirect($this->generateUrl('front', array('object_id' => $object->getId())));
        }

        return $response; 
    }*/


    /**
    * Creates a form to edit an entity.
    *
    * @param Object $object The object definition
    * @param Mixed $entity The entity 
    *
    * @return \Symfony\Component\Form\Form The form
    */
    /*private function createEditForm(Object $object, $entity)
    {
        //Variable initialisation
        $em = $this->getDoctrine()->getManager();
        $doctrineService = $this->get('sl_core.doctrine');
        $entityClass = $doctrineService->getEntityClass($object->getTechnicalName());

        //Form creation  
        $form = $this->createForm(new FrontType($em, $object, $entityClass), $entity, array(
            'action' => $this->generateUrl('front_update', array(
                'object_id' => $object->getId(), 
                'entity_id' => $entity->getId(),
                )
            ),
            'method' => 'PUT',
            'attr' => array(
                'class' => 'form-horizontal', 
                'valid-data-target' => '#'.$object->getTechnicalName().'_table_body', 
                'no-valid-data-target' => '#ajax-modal',
                ),
            )
        );

        //Submit button creation  
        $form->add('submit', 'submit', array(
            'label' => 'update',
            'attr' => array('class'=>'btn btn-primary btn-sm'),
            )
        );

        return $form;
    }*/

     /**
     * Displays a form to remove an existing entity.
     *
     * @ParamConverter("object", options={"mapping": {"object_id": "id"}})
     */
    /*public function removeAction(Object $object, $entity_id)
    {
        //Variable initialisation
        $databaseEm = $this->getDoctrine()->getManager('database');
        
        //Get entity to delete
        $entity = $databaseEm->getRepository('SLDataBundle:'.$object->getTechnicalName())->find($entity_id);

        //Form creation   
        $form = $this->createDeleteForm($object, $entity);

        //View creation 
        return $this->render('SLCoreBundle:Front:save.html.twig', array(
            'action' => 'delete',
            'form'   => $form->createView(),
            )
        );
    }*/

    /**
     * Deletes an entity.
     *
     * @ParamConverter("object", options={"mapping": {"object_id": "id"}})
     */
    /*public function deleteAction(Request $request, Object $object, $entity_id)
    {
        //Variable initialisation
        $databaseEm = $this->getDoctrine()->getManager('database');

        //Get entity to delete
        $entity = $databaseEm->getRepository('SLDataBundle:'.$object->getTechnicalName())->find($entity_id);

        //Form creation  
        $form = $this->createDeleteForm($object, $entity);

        //Associate return form data with entity
        $form->handleRequest($request);

        if ($request->isXmlHttpRequest()) {

            //Delete entity from database
            $databaseEm = $this->getDoctrine()->getManager('database');
            $databaseEm->remove($entity);

            //Create the Json Response array
            $html = "";
            $data = array(
                'formAction' => 'delete',  
                'html' => $html,
                'isValid' => true,
                'entityType' => $object->getTechnicalName(),
                'entityId' => $entity->getId(),
            );

            $databaseEm->flush();

            $response = new JsonResponse($data);
        }
        else {
            $response = $this->redirect($this->generateUrl('front', array('object_id' => $object->getId())));
        }   

        return $response;    
    }*/

    /**
     * Creates a form to delete an entity by id.
     *
     * @param Object $object The object definition
     * @param Mixed $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    /*private function createDeleteForm(Object $object, $entity)
    {
        //Variable initialisation
        $doctrineService = $this->get('sl_core.doctrine');
        $entityClass = $doctrineService->getEntityClass($object->getTechnicalName());

        //Form creation 
        $form = $this->createForm(new DeleteFrontType($object, $entityClass), $entity, array(
            'action' => $this->generateUrl('front_delete', array(
                'object_id' => $object->getId(), 
                'entity_id' => $entity->getId(),
                )
            ),
            'method' => 'DELETE',
            'attr' => array(
                'class' => 'form-horizontal', 
                'valid-data-target' => '#'.$object->getTechnicalName().'_table_body', 
                'no-valid-data-target' => '#ajax-modal',
                ),
            )
        );

        //Submit button creation    
        $form->add('submit', 'submit', array(
            'label' => 'delete',
            'attr' => array('class'=>'btn btn-danger btn-sm'),
            )
        );

        return $form;
    }*/

    /*public function treeViewAction() 
    {
        return $this->render('SLCoreBundle:Front:treeView.html.twig');
    }*/
}
