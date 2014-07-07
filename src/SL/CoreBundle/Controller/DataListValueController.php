<?php

namespace SL\CoreBundle\Controller;

//Symfony classes
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\DiExtraBundle\Annotation as DI;

//Custom classes
use SL\CoreBundle\Entity\DataList;
use SL\CoreBundle\Entity\DataListValue;
use SL\CoreBundle\Form\DataListValueType;

/**
 * DataListValue controller
 *
 */
class DataListValueController extends Controller
{
    private $em;
    private $jstreeService;
    private $iconService;
    private $classService;
    private $dataListValueService;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "jstreeService" = @DI\Inject("sl_core.jsTree"),
     *     "iconService" = @DI\Inject("sl_core.icon"),
     *     "classService" = @DI\Inject("sl_core.class"),
     *     "dataListValueService" = @DI\Inject("sl_core.dataListValue")
     * })
     */
    public function __construct($em, $jstreeService, $iconService, $classService, $dataListValueService)
    {
        $this->em = $em;
        $this->jstreeService = $jstreeService;
        $this->iconService = $iconService;
        $this->classService = $classService;
        $this->dataListValueService = $dataListValueService;
    }

    /**
     * Display form to create DataListValue.
     *
     * @param DataList $dataList Parent DataList of new DataListValue
     *
     */
    public function newAction(Request $request, DataList $dataList)
    {
        if ($request->isXmlHttpRequest()) {

            $dataListValue = new DataListValue();

            $form = $this->createCreateForm($dataList, $dataListValue);

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
     * Create Form action
     *
     * @param DataList $dataList Parent DataList of DataListValue
     *
     */
    public function createAction(Request $request, DataList $dataList)
    {
        $dataListValue = new DataListValue();
        $dataListValue->setDataList($dataList);

        $form = $this->createCreateForm($dataList, $dataListValue);

        $form->handleRequest($request);

        if ($request->isXmlHttpRequest()) {

            $isValid = $form->isValid();
            if ($isValid) {

                //Define DataListValue display position
                $maxDiplayOrder = $this->em->getRepository('SLCoreBundle:DataListValue')->findMaxDisplayOrder($dataList);
                $dataListValue->setDisplayOrder($maxDiplayOrder + 1);

                //Save DataListValue in database
                $this->em->persist($dataListValue);
                $this->em->flush();

                //Dont delete this flush : Persist data after Doctrine evenement
                $this->em->flush();
            }  
            
            $jsonResponse = $this->dataListValueService->createJsonResponse($dataListValue, $form); 

            $response = $jsonResponse;
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }

        return $response; 
    }

    /**
    * Create DataListValue form
    *
    * @param DataList $dataList Parent DataList of DataListValue
    * @param DataListValue $dataListValue DataListValue to create
    *
    * @return Form $form Create form
    *
    */
    private function createCreateForm(DataList $dataList, DataListValue $dataListValue)
    {     
        $form = $this->createForm(new DataListValueType(), $dataListValue, array(
            'action' => $this->generateUrl('data_list_value_create', array(
                'id' => $dataList->getId(),
                )
            ),
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
     * Display form to edit DataListValue
     *
     * @param DataListValue $dataListValue DataListValue to update
     *
     */
    public function editAction(DataListValue $dataListValue)
    {
        $form = $this->createEditForm($dataListValue);
   
        return $this->render('SLCoreBundle::save.html.twig', array(
            'entity' => $dataListValue,
            'form'   => $form->createView(),
            )
        );
    }

    /**
     * Update form action
     *
     * @param DataListValue $dataListValue DataListValue to update
     *
     */
    public function updateAction(Request $request, DataListValue $dataListValue)
    {
        $form = $this->createEditForm($dataListValue);
        
        $form->handleRequest($request);

        if ($request->isXmlHttpRequest()) {

            $isValid = $form->isValid();
            if ($isValid) {

                $this->em->flush();
                
                $dataList = $this->em->getRepository('SLCoreBundle:DataList')->findFullById($dataListValue->getDataList()->getId()); 

                $html = $this->renderView('SLCoreBundle:DataListValue:dataListValueTable.html.twig', array(
                    'dataList' => $dataList(), 
                    )
                );

                //Create DataListValue node in menu tree
                $nodeStructure = $this->jstreeService->updateDataListValueNode($dataListValue);
            }
            else {
                $html = $this->renderView('SLCoreBundle::save.html.twig', array(
                    'entity' => $dataListValue,
                    'form'   => $form->createView(),
                    )
                );
                $nodeStructure = null;
            }

            $data = array(  
                'form' => array(
                    'action' => strtolower($form->getConfig()->getMethod()),
                    'isValid' => $isValid,
                    ), 
                'html' => $html,
                'node' => array(
                    'nodeStructure' => $nodeStructure,
                    'nodeProperties' => null,
                ),
            );    

            $response = new JsonResponse($data);
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }

        return $response; 
    }


    /**
    * Update DataListValue form
    *
    * @param DataListValue $dataListValue DataListValue to update
    *
    * @return Form $form Update form
    *
    */
    private function createEditForm(DataListValue $dataListValue)
    {
        $form = $this->createForm(new DataListValueType(), $dataListValue, array(
            'action' => $this->generateUrl('data_list_value_update', array(
                'id' => $dataListValue->getId(),
                )),
            'method' => 'PUT',
            )
        );

        $form->add('submit', 'submit', array(
            'label' => 'update',
            'attr' => array('class'=>'btn btn-primary btn-sm'),
            )
        );

        return $form;
    }

    /**
     * Display form to remove DataListValue
     *
     * @param DataListValue $dataListValue DataListValue to delete
     *
     */
    public function removeAction(DataListValue $dataListValue)
    {
        $form = $this->createDeleteForm($dataListValue);

        return $this->render('SLCoreBundle::save.html.twig', array(
            'entity' => $dataListValue,
            'form'   => $form->createView(),
            )
        );
    }

    /**
     * Delete form action.
     *
     * @param DataListValue $dataListValue DataListValue to delete
     *
     */
    public function deleteAction(Request $request, DataListValue $dataListValue)
    {
        if ($request->isXmlHttpRequest()) {

            $nodeStructure = array(
                'id' => $dataListValue->getTechnicalName(),
            );

            $form = $this->createDeleteForm($dataListValue);

            $this->em->remove($dataListValue);
            $this->em->flush();

            $dataList = $this->em->getRepository('SLCoreBundle:DataList')->findFullById($dataListValue->getDataList()->getId()); 

            $html = $this->renderView('SLCoreBundle:DataListValue:dataListValueTable.html.twig', array(
                'dataList' => $dataList, 
                )
            );

            $data = array(  
                'form' => array(
                    'action' => strtolower($form->getConfig()->getMethod()),
                    'isValid' => true,
                    ),
                'html' => $html,
                'node' => array(
                    'nodeStructure' => $nodeStructure,
                    'nodeProperties' => null,
                ),
            );
            $response = new JsonResponse($data);
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }   

        return $response;    
    }


    /**
     * Delete DataListValue Form
     *
     * @param DataListValue $dataListValue DataListValue to delete
     *
     * @return Form $form Delete form
     */
    private function createDeleteForm(DataListValue $dataListValue)
    {
        $method = 'DELETE'; 

        $form = $this->createForm(new DataListValueType($method), $dataListValue, array(
            'action' => $this->generateUrl('data_list_value_delete', array(
                'id' => $dataListValue->getId(),
                )),
            'method' => $method,
            )
        );

        $form->add('submit', 'submit', array(
            'label' => 'delete',
            'attr' => array('class'=>'btn btn-danger btn-sm'),
            )
        );

        return $form;
    }

    /**
     * Update DataListValue icon.
     *
     * @param DataListValue $dataListValue DataListValue to update
     *
     */
    public function updateIconAction(Request $request, DataListValue $dataListValue)
    {
        if ($request->isXmlHttpRequest()) {

            $icon = $request->request->get('icon'); 

            $dataListValue->setIcon($icon); 
            $this->em->flush();

            $data = array(  
                'id' => $dataListValue->getTechnicalName(),
                'icon' => $this->iconService->getDataListValueIcon($dataListValue),
            );
            $response = new JsonResponse($data);
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }   

        return $response;    
    }

    /**
     * Update DataListValue checkbox.
     *
     * @param DataListValue $dataListValue DataListValue to update
     *
     */
    public function updateCheckboxAction(Request $request, DataListValue $dataListValue)
    {
        if ($request->isXmlHttpRequest()) {

            $value = ($request->request->get('value')=='true')?true:false;

            $dataListValue->setEnabled($value);      
            $this->em->flush();

            $response = new JsonResponse(
                array(
                    'id' => $dataListValue->getTechnicalName(),
                    'icon' => $this->iconService->getDataListValueIcon($dataListValue),
                    )
                );
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }   

        return $response;    
    }
}
