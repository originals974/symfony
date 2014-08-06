<?php

namespace SL\CoreBundle\Controller;

//Symfony classes
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

//Custom classes
use SL\CoreBundle\Entity\ChoiceList;
use SL\CoreBundle\Services\ChoiceListService;
SL\CoreBundle\Services\JSTreeService

/**
 * ChoiceList controller
 *
 */
class ChoiceListController extends Controller
{
    private $em;
    private $choiceListService;
    private $jstreeService;

     /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "choiceListService" = @DI\Inject("sl_core.choice_list"),
     *     "jstreeService" = @DI\Inject("sl_core.js_tree"),
     * })
     */
    public function __construct(EntityManager $em, ChoiceListService $choiceListService, JSTreeService $jstreeService)
    {
        $this->em = $em;
        $this->choiceListService = $choiceListService;
        $this->jstreeService = $jstreeService;
    }

    /**
     * Display choicelist create screen
     */
    public function indexAction(Request $request)
    {   
        if ($request->isXmlHttpRequest()) {
            $response = $this->render('SLCoreBundle:ChoiceList:index.html.twig');
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }

        return $response; 
    }

    /**
    * Display form to create choicelist entity
    */
    public function newAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {

            $choiceList = new ChoiceList();

            $form = $this->choiceListService->createCreateForm($choiceList);

            $response = $this->render('SLCoreBundle::save.html.twig', array(
                'entity' => $choiceList,
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
     * Create choicelist entity
     */
    public function createAction(Request $request)
    { 
        $choiceList = new ChoiceList();

        $form = $this->choiceListService->createCreateForm($choiceList);
        $form->handleRequest($request);

        if ($request->isXmlHttpRequest()) {
            
            $isValid = $form->isValid();
            if ($isValid) {

                $this->em->persist($choiceList);
                $this->em->flush();

                $html = null;
                $jsTree = $this->jstreeService->createNewChoiceListNode($choiceList); 
            } 
            else {
                $jsTree = null; 
                $html = $this->renderView('SLCoreBundle::save.html.twig', array(
                    'entity' => $choiceList,
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
    * Display form to edit choicelist entity
    *
    * @param ChoiceList $choiceList 
    */
    public function editAction(Request $request, ChoiceList $choiceList)
    {
        if ($request->isXmlHttpRequest()) {

            $form = $this->choiceListService->createEditForm($choiceList);
     
            $response = $this->render('SLCoreBundle::save.html.twig', array(
                'entity' => $choiceList,
                'form'   => $form->createView(),
                )
            );
        }
        else{
            $response = $this->redirect($this->generateUrl('back_end'));
        }

        return $response;
    }

    /**
    * Update choicelist entity
    *
    * @param ChoiceList $choiceList Choicelist to update
    */
    public function updateAction(Request $request, ChoiceList $choiceList)
    {
        $form = $this->choiceListService->createEditForm($choiceList);
        $form->handleRequest($request);

        if ($request->isXmlHttpRequest()) {

            $isValid = $form->isValid();
            if ($isValid) {

                $this->em->flush();

                $html = null; 
                $jsTree = $choiceList->getDisplayName();
            }
            else {
                $jsTree = null; 
                $html = $this->renderView('SLCoreBundle::save.html.twig', array(
                    'action' => 'update',
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
     * Show choicelist entity
     *
     * @param ChoiceList $choiceList Choicelist to show
     *
     * @ParamConverter("choiceList", options={"repository_method" = "fullFindById"})
     */
    public function showAction(Request $request, ChoiceList $choiceList)
    {
        if ($request->isXmlHttpRequest()) {

            $response = $this->render('SLCoreBundle:ChoiceList:show.html.twig', array(
                'choiceList' => $choiceList, 
                )
            );
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }

        return $response; 
    }

    /**
    * Display form to remove choicelist entity
    *
    * @param ChoiceList $choiceList
    */
    public function removeAction(Request $request, ChoiceList $choiceList)
    {
        if ($request->isXmlHttpRequest()) {

            //ChoiceList integrity control before delete
            $integrityError = $this->choiceListService->integrityControlBeforeDelete($choiceList); 
            if($integrityError == null) {
                  
                $form = $this->choiceListService->createDeleteForm($choiceList);

                $response = $this->render('SLCoreBundle::save.html.twig', array(
                    'entity' => $choiceList,
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
     * Delete choicelist entity
     *
     * @param ChoiceList $choiceList Choicelist to delete
     *
     */
    public function deleteAction(Request $request, ChoiceList $choiceList)
    {
        if ($request->isXmlHttpRequest()) {

            $this->em->remove($choiceList);
            $this->em->flush();

            $arrayResponse = array(
                'isValid' => true,
                'content' => array(
                    'html' => null,
                    'js_tree' => 'delete',
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
     * Update choicelist checkbox
     *
     * @param ChoiceList $choiceList Choicelist to update
     */
    public function updateCheckboxAction(Request $request, ChoiceList $choiceList)
    {
        if ($request->isXmlHttpRequest()) {

            $value = ($request->request->get('value')=='true')?true:false;

            $choiceList->setEnabled($value);     
 
            $this->em->flush();

            $response = new JsonResponse(null);
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }   

        return $response;    
    }
}
