<?php

namespace SL\CoreBundle\Controller\Choice;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use SL\CoreBundle\Entity\ChoiceList;
use SL\CoreBundle\Entity\ChoiceItem;
use SL\CoreBundle\Services\Choice\ChoiceItemService;
use SL\CoreBundle\Services\DoctrineService;

/**
 * ChoiceItem controller
 *
 */
class ChoiceItemController extends Controller
{
    private $em;
    private $choiceItemService;
    private $doctrineService;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "choiceItemService" = @DI\Inject("sl_core.choice_item"),
     *     "doctrineService" = @DI\Inject("sl_core.doctrine")
     * })
     */
    public function __construct(EntityManager $em, ChoiceItemService $choiceItemService, DoctrineService $doctrineService)
    {
        $this->em = $em;
        $this->choiceItemService = $choiceItemService;
        $this->doctrineService = $doctrineService;
    }

    /**
     * Display form to create a choice item 
     * associated to $choiceList
     *
     * @param Symfony\Component\HttpFoundation\Request $request
     * @param SL\CoreBundle\Entity\ChoiceList $choiceList
     *
     * @return Symfony\Component\HttpFoundation\Response $response
     */
    public function newAction(Request $request, ChoiceList $choiceList)
    {
        if ($request->isXmlHttpRequest()) {

            $choiceItem = new ChoiceItem();
            $form = $this->choiceItemService->createCreateForm($choiceList, $choiceItem);

            $response = $this->render('SLCoreBundle::save.html.twig', array(
                'entity' => $choiceItem,
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
     * Create a choice item 
     * associated to $choiceList
     *
     * @param Symfony\Component\HttpFoundation\Request $request
     * @param SL\CoreBundle\Entity\ChoiceList $choiceList
     *
     * @return Symfony\Component\HttpFoundation\JsonResponse $jsonResponse
     *
     * @ParamConverter("choiceList", options={"repository_method" = "fullFindById"})
     */
    public function createAction(Request $request, ChoiceList $choiceList)
    {
        $choiceItem = new ChoiceItem();
        $choiceItem->setChoiceList($choiceList); 
        $choiceList->addChoiceItem($choiceItem); 

        $form = $this->choiceItemService->createCreateForm($choiceList, $choiceItem);

        $form->handleRequest($request);

        if ($request->isXmlHttpRequest()) {

            if ($form->isValid()) {

                $this->em->persist($choiceItem);
                $this->em->flush();

                $html = $this->renderView('SLCoreBundle:Choice/ChoiceItem:table.html.twig', array(
                    'choiceList' => $choiceList, 
                    )
                );
            }
            else{

                $html = $this->renderView('SLCoreBundle::save.html.twig', array(
                    'entity' => $choiceItem,
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
 
            $jsonResponse = new JsonResponse($arrayResponse); 
        }
        else {
            $jsonResponse = $this->redirect($this->generateUrl('back_end'));
        }

        return $jsonResponse; 
    }

    /**
     * Display form to edit $choiceItem
     * associated to $choiceList
     *
     * @param Symfony\Component\HttpFoundation\Request $request
     * @param SL\CoreBundle\Entity\ChoiceList $choiceList
     * @param SL\CoreBundle\Entity\ChoiceItem $choiceItem
     *
     * @return Symfony\Component\HttpFoundation\Response $response
     *
     * @ParamConverter("choiceList", options={"id" = "choice_list_id", "repository_method" = "fullFindById"})
     */
    public function editAction(Request $request, ChoiceList $choiceList, ChoiceItem $choiceItem)
    {
        if ($request->isXmlHttpRequest()) {
            
            $form = $this->choiceItemService->createEditForm($choiceList, $choiceItem);
       
            $response = $this->render('SLCoreBundle::save.html.twig', array(
                'entity' => $choiceItem,
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
     * Update $choiceItem
     * associated to $choiceList
     *
     * @param Symfony\Component\HttpFoundation\Request $request
     * @param SL\CoreBundle\Entity\ChoiceList $choiceList
     * @param SL\CoreBundle\Entity\ChoiceItem $choiceItem
     *
     * @return Symfony\Component\HttpFoundation\JsonResponse $jsonResponse
     *
     * @ParamConverter("choiceList", options={"id" = "choice_list_id", "repository_method" = "fullFindById"})
     */
    public function updateAction(Request $request, ChoiceList $choiceList, ChoiceItem $choiceItem)
    {
        $form = $this->choiceItemService->createEditForm($choiceList, $choiceItem);
        $form->handleRequest($request);

        if ($request->isXmlHttpRequest()) {

            if ($form->isValid()) {

                $this->em->flush();
            
                $html = $this->renderView('SLCoreBundle:Choice/ChoiceItem:table.html.twig', array(
                    'choiceList' => $choiceList, 
                    )
                );
            }
            else {
                $html = $this->renderView('SLCoreBundle::save.html.twig', array(
                    'entity' => $choiceItem,
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
 
            $jsonResponse = new JsonResponse($arrayResponse); 
        }
        else {
            $jsonResponse = $this->redirect($this->generateUrl('back_end'));
        }

        return $jsonResponse;
    }

    /**
     * Display form to remove $choiceItem
     * associated to $choiceList
     *
     * @param Symfony\Component\HttpFoundation\Request $request
     * @param SL\CoreBundle\Entity\ChoiceList $choiceList
     * @param SL\CoreBundle\Entity\ChoiceItem $choiceItem
     *
     * @return Symfony\Component\HttpFoundation\Response $response
     *
     * @ParamConverter("choiceList", options={"id" = "choice_list_id", "repository_method" = "fullFindById"})
     */
    public function removeAction(Request $request, ChoiceList $choiceList, ChoiceItem $choiceItem)
    {
        if ($request->isXmlHttpRequest()) {

            $form = $this->choiceItemService->createDeleteForm($choiceList, $choiceItem);

            $response = $this->render('SLCoreBundle::save.html.twig', array(
                'entity' => $choiceItem,
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
     * Delete $choiceItem 
     * associated to $choiceList
     *
     * @param Symfony\Component\HttpFoundation\Request $request
     * @param SL\CoreBundle\Entity\ChoiceList $choiceList
     * @param SL\CoreBundle\Entity\ChoiceItem $choiceItem
     *
     * @return Symfony\Component\HttpFoundation\JsonResponse $jsonResponse 
     *
     * @ParamConverter("choiceList", options={"id" = "choice_list_id", "repository_method" = "fullFindById"})
     */
    public function deleteAction(Request $request, ChoiceList $choiceList, ChoiceItem $choiceItem)
    {
        if ($request->isXmlHttpRequest()) {

            $this->doctrineService->entityDelete('SLCoreBundle:ChoiceItem', $choiceItem->getId(), true);

            $html = $this->renderView('SLCoreBundle:Choice/ChoiceItem:table.html.twig', array(
                'choiceList' => $choiceList, 
                )
            );

            $arrayResponse = array(
                'isValid' => true,
                'content' => array(
                    'html' => $html,
                    'js_tree' => null,
                    ),
                );
 
            $jsonResponse = new JsonResponse($arrayResponse); 
        }
        else {
            $jsonResponse = $this->redirect($this->generateUrl('back_end'));
        }

        return $jsonResponse; 
    }

    /**
     * Update $choiceItem icon
     *
     * @param Symfony\Component\HttpFoundation\Request $request
     * @param SL\CoreBundle\Entity\ChoiceItem $choiceItem
     *
     * @return Symfony\Component\HttpFoundation\JsonResponse $jsonResponse
     */
    public function updateIconAction(Request $request, ChoiceItem $choiceItem)
    {
        if ($request->isXmlHttpRequest()) {

            $icon = $request->request->get('icon'); 

            $choiceItem->setIcon($icon); 
            $this->em->flush();

            $response = new JsonResponse(null);
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }   

        return $response;    
    }
}
