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
use SL\CoreBundle\Entity\ChoiceItem;
use SL\CoreBundle\Services\JSTreeService;
use SL\CoreBundle\Services\IconService;
use SL\CoreBundle\Services\ChoiceItemService;

/**
 * ChoiceItem controller
 *
 */
class ChoiceItemController extends Controller
{
    private $em;
    private $jstreeService;
    private $iconService;
    private $choiceItemService;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "jstreeService" = @DI\Inject("sl_core.js_tree"),
     *     "iconService" = @DI\Inject("sl_core.icon"),
     *     "choiceItemService" = @DI\Inject("sl_core.choice_item")
     * })
     */
    public function __construct(EntityManager $em, JSTreeService $jstreeService, IconService $iconService, ChoiceItemService $choiceItemService)
    {
        $this->em = $em;
        $this->jstreeService = $jstreeService;
        $this->iconService = $iconService;
        $this->choiceItemService = $choiceItemService;
    }

    /**
     * Display form to create choice item entity
     *
     * @param ChoiceList $choiceList Parent choice list
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
     * Create choice item entity
     *
     * @param ChoiceList $choiceList Parent choice list 
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

            $isValid = $form->isValid();
            if ($isValid) {

                $this->em->persist($choiceItem);
                $this->em->flush();

                $html = $this->renderView('SLCoreBundle:ChoiceItem:choiceItemTable.html.twig', array(
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
     * Display form to edit choice item entity
     *
     * @param ChoiceItem $choiceItem
     */
    public function editAction(ChoiceItem $choiceItem)
    {
        $form = $this->choiceItemService->createEditForm($choiceItem);
   
        return $this->render('SLCoreBundle::save.html.twig', array(
            'entity' => $choiceItem,
            'form'   => $form->createView(),
            )
        );
    }

    /**
     * Update choice item entity
     *
     * @param ChoiceItem $choiceItem
     */
    public function updateAction(Request $request, ChoiceItem $choiceItem)
    {
        $form = $this->ChoiceItemService->createEditForm($choiceItem);
        
        $form->handleRequest($request);

        if ($request->isXmlHttpRequest()) {

            $isValid = $form->isValid();
            if ($isValid) {

                $this->em->flush();
                
                $choiceList = $this->em->getRepository('SLCoreBundle:ChoiceList')->fullFindById($choiceItem->getChoiceList()->getId()); 

                $html = $this->renderView('SLCoreBundle:ChoiceItem:choiceItemTable.html.twig', array(
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
     * Display form to remove a choice item entity
     *
     * @param ChoiceItem $choiceItem
     */
    public function removeAction(ChoiceItem $choiceItem)
    {
        $form = $this->choiceItemService->createDeleteForm($choiceItem);

        return $this->render('SLCoreBundle::save.html.twig', array(
            'entity' => $choiceItem,
            'form'   => $form->createView(),
            )
        );
    }

    /**
     * Delete $choiceItem 
     *
     * @param ChoiceItem $choiceItem 
     */
    public function deleteAction(Request $request, ChoiceItem $choiceItem)
    {
        if ($request->isXmlHttpRequest()) {

            $this->em->remove($choiceItem);
            $this->em->flush();

            $choiceList = $this->em->getRepository('SLCoreBundle:ChoiceList')->fullFindById($choiceItem->getChoiceList()->getId()); 

            $html = $this->renderView('SLCoreBundle:ChoiceItem:choiceItemTable.html.twig', array(
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
 
            $response = new JsonResponse($arrayResponse); 
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }

        return $response; 
    }

    /**
     * Update $choiceItem icon
     *
     * @param ChoiceItem $choiceItem
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

    /**
     * Update $choiceItem checkbox
     *
     * @param ChoiceItem $choiceItem
     */
    public function updateCheckboxAction(Request $request, ChoiceItem $choiceItem)
    {
        if ($request->isXmlHttpRequest()) {

            $value = ($request->request->get('value')=='true')?true:false;

            $choiceItem->setEnabled($value);      
            $this->em->flush();

            $response = new JsonResponse(null);
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }   

        return $response;    
    }
}
