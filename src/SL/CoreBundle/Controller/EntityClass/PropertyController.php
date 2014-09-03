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
use SL\CoreBundle\Services\EntityService;

/**
 * Property controller
 *
 */
class PropertyController extends Controller
{
    private $em;
    private $propertyService;
    private $doctrineService;
    private $entityService;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "propertyService" = @DI\Inject("sl_core.property"),
     *     "doctrineService" = @DI\Inject("sl_core.doctrine"),
     *     "entityService" = @DI\Inject("sl_core.entity")
     * })
     */
    public function __construct(EntityManager $em, PropertyService $propertyService, DoctrineService $doctrineService, EntityService $entityService)
    {
        $this->em = $em;
        $this->propertyService = $propertyService;
        $this->doctrineService = $doctrineService;
        $this->entityService = $entityService;
    }

    /**
     * Display form to create a property
     * associated to $entityClass
     *
     * @param Request $request
     * @param EntityClass $entityClass
     *
     * @return Response $response
     */
    public function newAction(Request $request, EntityClass $entityClass)
    {
        if ($request->isXmlHttpRequest()) {

            $property = new Property(null, $entityClass);
            $formArray = $this->propertyService->createCreateForm($property);
            $selectForm = $formArray['selectForm']; 
            $mainform = $formArray['mainForm'];

            $response = $this->render('SLCoreBundle::save.html.twig', array(
                'entity' => $property,
                'selectForm' => $selectForm->createView(),
                'form'   => $mainform->createView(),
                )
            );
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }

        return $response; 
    }
    
    /**
     * Display form to create selected property 
     * associated to $entityClass
     *
     * @param Request $request
     * @param EntityClass $entityClass
     *
     * @return Response $response
     */
    public function selectFormAction(Request $request, EntityClass $entityClass)
    {
        if ($request->isXmlHttpRequest()) {

            $formMode = $request->query->get('formMode'); 
            
            $property = $this->propertyService->getPropertyEntityClassByFormMode($formMode, $entityClass); 

            $formArray = $this->propertyService->createCreateForm($property, $formMode);
            $selectForm = $formArray['selectForm']; 
            $mainform = $formArray['mainForm'];

            $response = $this->render('SLCoreBundle::save.html.twig', array(
                'entity' => $property,
                'selectForm' => $selectForm->createView(),
                'form'   => $mainform->createView(),
                )
            );
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }

        return $response;
    }

    /**
     * Create a property with $formMode type
     * associated to $entityClass
     *
     * @param Request $request
     * @param EntityClass $entityClass
     * @param string $formMode Determine type of new property <default(text, money, date,...)|entity|choice>
     *
     * @return Mixed $response
     *
     * @ParamConverter("entityClass", options={"id" = "entity_class_id", "repository_method" = "fullFindById"})
     */
    public function createAction(Request $request, EntityClass $entityClass, $formMode)
    {
        $property = $this->propertyService->getPropertyEntityClassByFormMode($formMode, $entityClass); 

        $formArray = $this->propertyService->createCreateForm($property, $formMode);
        $selectForm = $formArray['selectForm'];
        $form = $formArray['mainForm'];

        $form->handleRequest($request);

        if ($request->isXmlHttpRequest()) {

            if ($form->isValid()) {

                $this->em->persist($property);
                $this->em->flush();

                $this->doctrineService->generateEntityFileAndObjectSchema($entityClass);  

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
     * Display form to edit $property
     *
     * @param Request $request
     * @param Property $property
     *
     * @return Symfony\Component\HttpFoundation\Response $response
     */
    public function editAction(Request $request, Property $property)
    {
        if ($request->isXmlHttpRequest()) {
            $form = $this->propertyService->createEditForm($property);
     
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
     * Update $property
     * associated to $entityClass
     *
     * @param Request $request
     * @param EntityClass $entityClass
     * @param Property $property
     *
     * @return Mixed $response
     *
     * @ParamConverter("entityClass", options={"id" = "entity_class_id", "repository_method" = "fullFindById"})
     */
    public function updateAction(Request $request, EntityClass $entityClass, Property $property)
    {
        $form = $this->propertyService->createEditForm($property);
        $form->handleRequest($request);

        if ($request->isXmlHttpRequest()) {

            if ($form->isValid()) {

                $this->em->flush();
                      
                //Update database schema
                $this->doctrineService->generateEntityFileAndObjectSchema($entityClass);  

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
     * Display form to remove $property
     *
     * @param Request $request
     * @param Property $property
     *
     * @return Symfony\Component\HttpFoundation\Response $response
     *
     */
    public function removeAction(Request $request, Property $property)
    {
        if ($request->isXmlHttpRequest()) {

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
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }

        return $response;
    }

    /**
     * Delete $property 
     * associated to $entityClass
     *
     * @param Request $request
     * @param EntityClass $entityClass
     * @param Property $property
     *
     * @return Mixed $response 
     *
     * @ParamConverter("entityClass", options={"id" = "entity_class_id", "repository_method" = "fullFindById"})
     */
    public function deleteAction(Request $request, EntityClass $entityClass, Property $property)
    {
        if ($request->isXmlHttpRequest()) {
            
            if($this->entityService->propertyHasNotNullValues($property)){
                $this->doctrineService->entityDelete($property, false);
            } 
            else {
                $this->doctrineService->entityDelete($property, true);

                //Update doctrine entity and schema
                $this->doctrineService->generateEntityFileAndObjectSchema($entityClass);  
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
     * Update $property checkbox 
     *
     * @param Request $request
     * @param Property $property
     *
     * @return Mixed $response 
     */
    public function updateCheckboxAction(Request $request, Property $property)
    {
        if ($request->isXmlHttpRequest()) {

            $value = ($request->request->get('value')=='true')?true:false;

            $property->setRequired($value);
            $this->em->flush();

            $this->doctrineService->generateEntityFileAndObjectSchema($property->getEntityClass());  
            
            $response = new Response();
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }   

        return $response;    
    }
}
