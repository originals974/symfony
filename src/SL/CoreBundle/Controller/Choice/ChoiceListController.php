<?php

namespace SL\CoreBundle\Controller\Choice;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use SL\CoreBundle\Entity\Choice\ChoiceList;
use SL\CoreBundle\Services\Choice\ChoiceListService;
use SL\CoreBundle\Services\DoctrineService;
use SL\CoreBundle\Services\JSTreeService;

/**
 * ChoiceList controller
 *
 */
class ChoiceListController extends Controller
{
    private $em;
    private $choiceListService;
    private $doctrineService;
    private $jstreeService;

     /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "choiceListService" = @DI\Inject("sl_core.choice_list"),
     *     "doctrineService" = @DI\Inject("sl_core.doctrine"),
     *     "jstreeService" = @DI\Inject("sl_core.js_tree"),
     * })
     */
    public function __construct(EntityManager $em, ChoiceListService $choiceListService, DoctrineService $doctrineService, JSTreeService $jstreeService)
    {
        $this->em = $em;
        $this->choiceListService = $choiceListService;
        $this->doctrineService = $doctrineService;
        $this->jstreeService = $jstreeService;
    }

    /**
     * Display choice list main screen
     *
     * @param Symfony\Component\HttpFoundation\Request $request
     *
     * @return Symfony\Component\HttpFoundation\Response $response 
     */
    public function indexAction(Request $request)
    {   
        if ($request->isXmlHttpRequest()) {
            $response = $this->render('SLCoreBundle:Choice/ChoiceList:index.html.twig');
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }

        return $response; 
    }

    /**
    * Display form to create a choice list
    *
    * @param Symfony\Component\HttpFoundation\Request $request
    *
    * @return Symfony\Component\HttpFoundation\Response $response
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
     * Create a choice list
     *
     * @param Symfony\Component\HttpFoundation\Request $request
     *
     * @return Mixed $response
     */
    public function createAction(Request $request)
    { 
        $choiceList = new ChoiceList();

        $form = $this->choiceListService->createCreateForm($choiceList);
        $form->handleRequest($request);

        if ($request->isXmlHttpRequest()) {

            if ($form->isValid()) {

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
                'isValid' => $form->isValid(),
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
    * Display form to edit $choiceList
    *
    * @param Symfony\Component\HttpFoundation\Request $request
    * @param SL\CoreBundle\Entity\Choice\ChoiceList $choiceList 
    *
    * @return Symfony\Component\HttpFoundation\Response $response
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
    * Update $choiceList
    *
    * @param Symfony\Component\HttpFoundation\Request $request
    * @param SL\CoreBundle\Entity\Choice\ChoiceList $choiceList
    *
    * @return Mixed $response
    */
    public function updateAction(Request $request, ChoiceList $choiceList)
    {
        $form = $this->choiceListService->createEditForm($choiceList);
        $form->handleRequest($request);

        if ($request->isXmlHttpRequest()) {

            if ($form->isValid()) {

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
                'isValid' => $form->isValid(),
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
     * Show $choiceList and its items
     *
     * @param Symfony\Component\HttpFoundation\Request $request
     * @param SL\CoreBundle\Entity\Choice\ChoiceList $choiceList
     *
     * @return Symfony\Component\HttpFoundation\Response $response
     *
     * @ParamConverter("choiceList", options={"repository_method" = "fullFindById"})
     */
    public function showAction(Request $request, ChoiceList $choiceList)
    {
        if ($request->isXmlHttpRequest()) {

            $response = $this->render('SLCoreBundle:Choice/ChoiceList:show.html.twig', array(
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
    * Display form to remove $choiceList
    *
    * @param Symfony\Component\HttpFoundation\Request $request
    * @param SL\CoreBundle\Entity\Choice\ChoiceList $choiceList
    *
    * @return Symfony\Component\HttpFoundation\Response $response
    */
    public function removeAction(Request $request, ChoiceList $choiceList)
    {
        if ($request->isXmlHttpRequest()) {

            //Choice list integrity control before delete
            $integrityError = $this->choiceListService->integrityControlBeforeDelete($choiceList); 
            if($integrityError === null) {
                  
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
     * Delete choice list identified by $id
     *
     * @param Symfony\Component\HttpFoundation\Request $request
     * @param integer $id
     *
     * @return Mixed $response
     */
    public function deleteAction(Request $request, $id)
    {
        if ($request->isXmlHttpRequest()) {

            $this->doctrineService->entityDelete('SLCoreBundle:Choice\ChoiceList', $id, true);

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
}
