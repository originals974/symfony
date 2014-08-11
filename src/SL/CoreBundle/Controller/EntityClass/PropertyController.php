<?php

namespace SL\CoreBundle\Controller\EntityClass;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\DiExtraBundle\Annotation as DI;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use SL\CoreBundle\Entity\EntityClass\EntityClass;
use SL\CoreBundle\Entity\EntityClass\Property;
use SL\CoreBundle\Services\EntityClass\PropertyService;
use SL\CoreBundle\Services\DoctrineService;
use SL\CoreBundle\Services\FrontService;

/**
 * Property controller
 *
 */
class PropertyController extends Controller
{
    private $em;
    private $propertyService;
    private $doctrineService;
    private $frontService;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "propertyService" = @DI\Inject("sl_core.property"),
     *     "doctrineService" = @DI\Inject("sl_core.doctrine"),
     *     "frontService" = @DI\Inject("sl_core.front")
     * })
     */
    public function __construct(EntityManager $em, PropertyService $propertyService, DoctrineService $doctrineService, FrontService $frontService)
    {
        $this->em = $em;
        $this->propertyService = $propertyService;
        $this->doctrineService = $doctrineService;
        $this->frontService = $frontService;
    }

    /**
     * Display form to create property entity
     *
     * @param EntityClass\EntityClass $entityClass Parent entityClass of new property
     */
    public function newAction(Request $request, EntityClass $entityClass)
    {
        if ($request->isXmlHttpRequest()) {

            $property = new Property();
 
            $formArray = $this->propertyService->createCreateForm($entityClass, $property, 'default');
            $selectForm = $formArray['selectForm']; 
            $form = $formArray['mainForm'];

            $response = $this->render('SLCoreBundle::save.html.twig', array(
                'entity' => $property,
                'selectForm' => $selectForm->createView(),
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
     * @param EntityClass\EntityClass $entityClass Parent entityClass of new property
     */
    public function selectFormAction(Request $request, EntityClass $entityClass)
    {
        if ($request->isXmlHttpRequest()) {

            $formMode = $request->query->get('formMode'); 

            //Create property 
            $property = $this->propertyService->getPropertyEntityClassByFormMode($formMode); 
            $property->setEntityClass($entityClass); 

            $formArray = $this->propertyService->createCreateForm($entityClass, $property, $formMode);
            $selectForm = $formArray['selectForm']; 
            $form = $formArray['mainForm'];

            $response = $this->render('SLCoreBundle::save.html.twig', array(
                'entity' => $property,
                'selectForm' => $selectForm->createView(),
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
     * @param EntityClass\EntityClass $entityClass Parent entityClass of new property
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
        $selectForm = $formArray['selectForm'];
        $form = $formArray['mainForm'];

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

                $html = $this->renderView('SLCoreBundle:EntityClass/Property:table.html.twig', array(
                    'entityClass' => $entityClass, 
                    )
                );
            }
            else {
                $html = $this->renderView('SLCoreBundle::save.html.twig', array(
                    'entity' => $property,
                    'selectForm' => $selectForm,
                    'form'   => $form,
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
     * @param EntityClass\Property $property
     * @ParamConverter("entityClass", options={"id" = "entity_class_id", "repository_method" = "fullFindById"})
     */
    public function editAction(Request $request, EntityClass $entityClass, Property $property)
    {
        if ($request->isXmlHttpRequest()) {
            $form = $this->propertyService->createEditForm($entityClass, $property);
     
            return $this->render('SLCoreBundle::save.html.twig', array(
                'entity' => $property,
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
     * Update property entity
     *
     * @param EntityClass\Property $property Property to update
     *
     * @ParamConverter("entityClass", options={"id" = "entity_class_id", "repository_method" = "fullFindById"})
     */
    public function updateAction(Request $request, EntityClass $entityClass, Property $property)
    {
        $form = $this->propertyService->createEditForm($entityClass, $property);

        $form->handleRequest($request);

        if ($request->isXmlHttpRequest()) {

            if ($form->isValid()) {

                $this->em->flush();
                      
                //Update database schema
                $this->doctrineService->doctrineGenerateEntityFileByEntityClass($property->getEntityClass());  
                $this->doctrineService->doctrineSchemaUpdateForce();

                $html = $this->renderView('SLCoreBundle:EntityClass/Property:table.html.twig', array(
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
     * Display form to remove property entity
     *
     * @param EntityClass\Property $property
     * @ParamConverter("entityClass", options={"id" = "entity_class_id", "repository_method" = "fullFindById"})
     */
    public function removeAction(Request $request, EntityClass $entityClass, Property $property)
    {
        if ($request->isXmlHttpRequest()) {
            //Property integrity control before delete
            $integrityError = $this->propertyService->integrityControlBeforeDelete($property); 
            if($integrityError == null) {
                       
                $form = $this->propertyService->createDeleteForm($entityClass, $property);

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
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }

        return $response;
    }

    /**
     * Delete property entity
     *
     * @param EntityClass\Property $property Property to delete
     * @ParamConverter("entityClass", options={"id" = "entity_class_id", "repository_method" = "fullFindById"})
     */
    public function deleteAction(Request $request, EntityClass $entityClass, Property $property)
    {
        if ($request->isXmlHttpRequest()) {
            
            if($this->frontService->propertyHasNotNullValues($property)){
                $this->doctrineService->entityDelete('SLCoreBundle:EntityClass\Property', $property->getId(), false);
            } 
            else {
                $this->doctrineService->entityDelete('SLCoreBundle:EntityClass\Property', $property->getId(), true);

                //Update doctrine entity and schema
                $this->doctrineService->doctrineGenerateEntityFileByEntityClass($entityClass);  
                $this->doctrineService->doctrineSchemaUpdateForce();
            }

            $html = $this->renderView('SLCoreBundle:EntityClass/Property:table.html.twig', array(
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
     * @param EntityClass\Property $property Property to update
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
