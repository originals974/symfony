<?php

namespace SL\CoreBundle\Controller;

//Symfony classes
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\DiExtraBundle\Annotation as DI;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

//Custom classes
use SL\CoreBundle\Entity\EntityClass;
use SL\CoreBundle\Entity\Property;
use SL\CoreBundle\Services\PropertyService;
use SL\CoreBundle\Services\DoctrineService;

/**
 * Property controller
 *
 */
class PropertyController extends Controller
{
    private $em;
    private $propertyService;
    private $doctrineService;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "propertyService" = @DI\Inject("sl_core.property"),
     *     "doctrineService" = @DI\Inject("sl_core.doctrine")
     * })
     */
    public function __construct(EntityManager $em, PropertyService $propertyService, DoctrineService $doctrineService)
    {
        $this->em = $em;
        $this->propertyService = $propertyService;
        $this->doctrineService = $doctrineService;
    }

    /**
     * Display form to create property entity
     *
     * @param EntityClass $entityClass Parent entityClass of new property
     */
    public function newAction(Request $request, EntityClass $entityClass)
    {
        if ($request->isXmlHttpRequest()) {

            $property = new Property();
 
            $formArray = $this->propertyService->createCreateForm($entityClass, $property, 'default');
            $formChoice = $formArray['choiceForm']; 
            $form = $formArray['mainForm'];

            $response = $this->render('SLCoreBundle::save.html.twig', array(
                'entity' => $property,
                'formChoice' => $formChoice->createView(),
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
     * Display form to choose property type (default, entity, list) 
     *
     * @param EntityClass $entityClass Parent entityClass of new property
     */
    public function choiceFormAction(Request $request, EntityClass $entityClass)
    {
        if ($request->isXmlHttpRequest()) {

            $formMode = $request->request->get('formMode'); 

            //Create property 
            $property = $this->propertyService->getPropertyEntityClassByFormMode($formMode); 
            $property->setEntityClass($entityClass); 

            $formArray = $this->propertyService->createCreateForm($entityClass, $property, $formMode);
            $formChoice = $formArray['choiceForm']; 
            $form = $formArray['mainForm'];

            $response = $this->render('SLCoreBundle::save.html.twig', array(
                'entity' => $property,
                'formChoice' => $formChoice->createView(),
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
     * Create property entity
     *
     * @param EntityClass $entityClass Parent entityClass of new property
     * @param String $formMode Property type to create (Default | Entity | List) 
     * 
     * @ParamConverter("entityClass", options={"repository_method" = "fullFindById"})
     */
    public function createAction(Request $request, EntityClass $entityClass, $formMode)
    {
        $property = $this->propertyService->getPropertyEntityClassByFormMode($formMode); 
        $property->setEntityClass($entityClass);
        $entityClass->addProperty($property); 

        $formArray = $this->propertyService->createCreateForm($entityClass, $property, $formMode);

        $form->handleRequest($request);

        if ($request->isXmlHttpRequest()) {

            if ($form->isValid()) {

                if($formMode == 'entity' || $formMode == 'choice') {
                    $fieldType = $this->em->getRepository('SLCoreBundle:FieldType')->findOneByFormType($formMode);
                    $property->setFieldType($fieldType); 
                }

                $this->em->persist($property);
                $this->em->flush();

                //Update database schema
                $this->doctrineService->doctrineGenerateEntityFileByEntityClass($entityClass);  
                $this->doctrineService->doctrineSchemaUpdateForce();

                $html = $this->renderView('SLCoreBundle:Property:propertyTable.html.twig', array(
                    'entityClass' => $entityClass, 
                    )
                );
            }
            else {
                $html = $this->renderView('SLCoreBundle::save.html.twig', array(
                    'entity' => $property,
                    'formChoice' => $formArray['choiceForm']->createView(),
                    'form'   => $formArray['mainForm']->createView(),
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
     * Display form to edit property entity
     *
     * @param Property $property
     */
    public function editAction(Property $property)
    {
        $form = $this->propertyService->createEditForm($property);
 
        return $this->render('SLCoreBundle::save.html.twig', array(
            'entity' => $property,
            'form'   => $form->createView(),
            )
        );
    }

    /**
     * Update property entity
     *
     * @param Property $property Property to update
     */
    public function updateAction(Request $request, Property $property)
    {
        $form = $this->propertyService->createEditForm($property);

        $form->handleRequest($request);

        if ($request->isXmlHttpRequest()) {

            $isValid = $form->isValid();
            if ($isValid) {

                $this->em->flush();
                      
                //Update database schema
                $this->doctrineService->doctrineGenerateEntityFileByEntityClass($property->getEntityClass());  
                $this->doctrineService->doctrineSchemaUpdateForce();

                $entityClass = $this->em->getRepository('SLCoreBundle:EntityClass')->fullFindById($property->getEntityClass()->getId()); 

                $html = $this->renderView('SLCoreBundle:Property:propertyTable.html.twig', array(
                    'entityClass' => $entityClass, 
                    )
                );
            }
            else {
                $html = $this->renderView('SLCoreBundle::save.html.twig', array(
                    'entity' => $property,
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
     * Display form to remove property entity
     *
     * @param Property $property
     */
    public function removeAction(Property $property)
    {
        //Property integrity control before delete
        $integrityError = $this->propertyService->integrityControlBeforeDelete($property); 
        if($integrityError == null) {
                   
            $form = $this->propertyService->createDeleteForm($property);

            return $this->render('SLCoreBundle::save.html.twig', array(
                'entity' => $property,
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

        return $response;
    }

    /**
     * Delete property entity
     *
     * @param Property $property Property to delete
     */
    public function deleteAction(Request $request, Property $property)
    {
        if ($request->isXmlHttpRequest()) {
            
            $this->em->remove($property);
            $this->em->flush();
           
            $entityClass = $this->em->getRepository('SLCoreBundle:EntityClass')->fullFindById($property->getEntityClass()->getId()); 

            $html = $this->renderView('SLCoreBundle:Property:propertyTable.html.twig', array(
                'entityClass' => $entityClass, 
                )
            );

            $arrayResponse = array(
                'isValid' => true,
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
     * Update property checkbox
     *
     * @param Property $property Property to update
     */
    public function updateCheckboxAction(Request $request, Property $property)
    {
        if ($request->isXmlHttpRequest()) {

            $name = $request->request->get('name'); 
            $value = ($request->request->get('value')=='true')?true:false;

            switch ($name) {
                case 'isRequired':
                    $property->setRequired($value);
                    break;
            }
          
            $this->em->flush();

            if($name == 'isRequired') {
                //Update database schema
                $this->doctrineService->doctrineGenerateEntityFileByEntityClass($property->getEntityClass());  
                $this->doctrineService->doctrineSchemaUpdateForce();
            }

            $response = new Response();
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }   

        return $response;    
    }
}
