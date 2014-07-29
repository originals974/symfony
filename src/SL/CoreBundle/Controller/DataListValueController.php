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
use SL\CoreBundle\Entity\DataList;
use SL\CoreBundle\Entity\DataListValue;
use SL\CoreBundle\Services\JSTreeService;
use SL\CoreBundle\Services\IconService;
use SL\CoreBundle\Services\DataListValueService;

/**
 * DataListValue controller
 *
 */
class DataListValueController extends Controller
{
    private $em;
    private $jstreeService;
    private $iconService;
    private $dataListValueService;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "jstreeService" = @DI\Inject("sl_core.js_tree"),
     *     "iconService" = @DI\Inject("sl_core.icon"),
     *     "dataListValueService" = @DI\Inject("sl_core.data_list_value")
     * })
     */
    public function __construct(EntityManager $em, JSTreeService $jstreeService, IconService $iconService, DataListValueService $dataListValueService)
    {
        $this->em = $em;
        $this->jstreeService = $jstreeService;
        $this->iconService = $iconService;
        $this->dataListValueService = $dataListValueService;
    }

    /**
     * Display form to create datalistvalue entity
     *
     * @param DataList $dataList Parent datalist
     */
    public function newAction(Request $request, DataList $dataList)
    {
        if ($request->isXmlHttpRequest()) {

            $dataListValue = new DataListValue();

            $form = $this->dataListValueService->createCreateForm($dataList, $dataListValue);

            $response = $this->render('SLCoreBundle::save.html.twig', array(
                'entity' => $dataListValue,
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
     * Create datalistvalue entity
     *
     * @param DataList $dataList Parent datalist 
     *
     * @ParamConverter("dataList", options={"repository_method" = "findFullById"})
     */
    public function createAction(Request $request, DataList $dataList)
    {
        $dataListValue = new DataListValue();
        $dataListValue->setDataList($dataList); 
        $dataList->addDataListValue($dataListValue); 

        $form = $this->dataListValueService->createCreateForm($dataList, $dataListValue);

        $form->handleRequest($request);

        if ($request->isXmlHttpRequest()) {

            $isValid = $form->isValid();
            if ($isValid) {

                $this->em->persist($dataListValue);
                $this->em->flush();

                $html = $this->renderView('SLCoreBundle:DataListValue:dataListValueTable.html.twig', array(
                    'dataList' => $dataList, 
                    )
                );
            }
            else{

                $html = $this->renderView('SLCoreBundle::save.html.twig', array(
                    'entity' => $dataListValue,
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
     * Display form to edit datalistvalue entity
     *
     * @param DataListValue $dataListValue
     */
    public function editAction(DataListValue $dataListValue)
    {
        $form = $this->dataListValueService->createEditForm($dataListValue);
   
        return $this->render('SLCoreBundle::save.html.twig', array(
            'entity' => $dataListValue,
            'form'   => $form->createView(),
            )
        );
    }

    /**
     * Update datalistvalue entity
     *
     * @param DataListValue $dataListValue Datalistvalue to update
     */
    public function updateAction(Request $request, DataListValue $dataListValue)
    {
        $form = $this->dataListValueService->createEditForm($dataListValue);
        
        $form->handleRequest($request);

        if ($request->isXmlHttpRequest()) {

            $isValid = $form->isValid();
            if ($isValid) {

                $this->em->flush();
                
                $dataList = $this->em->getRepository('SLCoreBundle:DataList')->findFullById($dataListValue->getDataList()->getId()); 

                $html = $this->renderView('SLCoreBundle:DataListValue:dataListValueTable.html.twig', array(
                    'dataList' => $dataList, 
                    )
                );
            }
            else {
                $html = $this->renderView('SLCoreBundle::save.html.twig', array(
                    'entity' => $dataListValue,
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
     * Display form to remove datalistvalue entity
     *
     * @param DataListValue $dataListValue
     */
    public function removeAction(DataListValue $dataListValue)
    {
        $form = $this->dataListValueService->createDeleteForm($dataListValue);

        return $this->render('SLCoreBundle::save.html.twig', array(
            'entity' => $dataListValue,
            'form'   => $form->createView(),
            )
        );
    }

    /**
     * Delete datalistvalue entity
     *
     * @param DataListValue $dataListValue Datalistvalue to delete
     */
    public function deleteAction(Request $request, DataListValue $dataListValue)
    {
        if ($request->isXmlHttpRequest()) {

            $this->em->remove($dataListValue);
            $this->em->flush();

            $dataList = $this->em->getRepository('SLCoreBundle:DataList')->findFullById($dataListValue->getDataList()->getId()); 

            $html = $this->renderView('SLCoreBundle:DataListValue:dataListValueTable.html.twig', array(
                'dataList' => $dataList, 
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
     * Update Datalistvalue icon
     *
     * @param DataListValue $dataListValue Datalistvalue to update
     */
    public function updateIconAction(Request $request, DataListValue $dataListValue)
    {
        if ($request->isXmlHttpRequest()) {

            $icon = $request->request->get('icon'); 

            $dataListValue->setIcon($icon); 
            $this->em->flush();

            $response = new JsonResponse(null);
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }   

        return $response;    
    }

    /**
     * Update Datalistvalue checkbox
     *
     * @param DataListValue $dataListValue Datalistvalue to update
     */
    public function updateCheckboxAction(Request $request, DataListValue $dataListValue)
    {
        if ($request->isXmlHttpRequest()) {

            $value = ($request->request->get('value')=='true')?true:false;

            $dataListValue->setEnabled($value);      
            $this->em->flush();

            $response = new JsonResponse(null);
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }   

        return $response;    
    }
}
