<?php

namespace SL\CoreBundle\Controller;

//Symfony classes
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;

//Custom classes
use SL\CoreBundle\Entity\Object;
use SL\CoreBundle\Services\ObjectService;

/**
 * Object Update controller
 *
 */
class ObjectUpdateController extends Controller
{
    private $em;
    private $objectService;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "objectService" = @DI\Inject("sl_core.object"),
     * })
     */
    public function __construct(EntityManager $em, ObjectService $objectService)
    {
        $this->em = $em;
        $this->objectService = $objectService;
    }

     /**
    * Display form to edit object entity
    *
    * @param Object $object
    */
    public function editAction(Object $object)
    {
        $form = $this->objectService->createEditForm($object);
 
        return $this->render('SLCoreBundle::save.html.twig', array(
            'entity' => $object,
            'form'   => $form->createView(),
            )
        );
    }

    /**
    * Update object entity
    *
    * @param Object $object Object to update
    */
    public function updateAction(Request $request, Object $object)
    {
        $form = $this->objectService->createEditForm($object);
        $form->handleRequest($request);

        if ($request->isXmlHttpRequest()) {

            $isValid = $form->isValid();
            if ($isValid) {

                $this->em->flush();
                
                $jsTree = $object->getDisplayName();
                $html = null; 
                
            }
            else {
                $jsTree = null;
                $html = $this->renderView('SLCoreBundle::save.html.twig', array(
                    'entity' => $object,
                    'form'   => $form->createView(),
                    )
                );
            }
            
            $arrayResponse = array(
                'isValid' => $isValid,
                'content' => array(
                    'html' => $html,
                    'js_tree' => $jsTree,
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
    * Display form to edit calculated name
    *
    * @param Object $object
    */
    public function editCalculatedNameAction(Object $object)
    {
        $form = $this->objectService->createEditCalculatedNameForm($object);
 
        $objects = $this->em->getRepository('SLCoreBundle:Object')->getPath($object); 

        return $this->render('SLCoreBundle:Object:objectNameDesigner.html.twig', array(
            'objects' => $objects,
            'form'   => $form->createView(),
            )
        );
    }

    /**
    * Update object entity
    *
    * @param Object $object Object to update
    */
    public function updateCalculatedNameAction(Request $request, Object $object)
    {
        $form = $this->objectService->createEditCalculatedNameForm($object);

        $form->handleRequest($request);

        if ($request->isXmlHttpRequest()) {

            $isValid = $form->isValid();
            if ($isValid) {

                if($form->get('updateExistingName')->getData()) {
                    //Refresh display name of existing data
                    $this->objectService->refreshCalculatedName($object); 
                }

                $this->em->flush();

                $html = null; 
            }
            else {
                $objects = $this->em->getRepository('SLCoreBundle:Object')->getPath($object); 
                $html = $this->renderView('SLCoreBundle:Object:objectNameDesigner.html.twig', array(
                    'objects' => $objects,
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
     * Update object icon
     *
     * @param Object $object Object to update
     */
    public function updateIconAction(Request $request, Object $object)
    {
        if ($request->isXmlHttpRequest()) {

            $icon = $request->request->get('icon'); 

            if($icon != $object->getIcon()) {
                $object->setIcon($icon); 
                $this->em->flush();
            }

            $response = new JsonResponse(null);
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }   

        return $response;    
    }

    /**
     * Update object checkbox
     *
     * @param Object $object Object to update
     *
     */
    public function updateCheckboxAction(Request $request, Object $object)
    {
        if ($request->isXmlHttpRequest()) {

            $value = ($request->request->get('value')=='true')?true:false;

            $object->setEnabled($value);     
            $this->em->flush();

            $response = new JsonResponse(null);
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }   

        return $response;    
    }
}
