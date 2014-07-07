<?php

namespace SL\CoreBundle\Controller;

//Symfony classes
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;

//Custom classes
use SL\CoreBundle\Entity\DataList;
use SL\CoreBundle\Form\DataListType;
use SL\CoreBundle\Services\DataListService;
use SL\CoreBundle\Services\JSTreeService;
use SL\CoreBundle\Services\IconService;

/**
 * DataList controller
 *
 */
class DataListController extends Controller
{
    private $em;
    private $dataListService;
    private $jstreeService;
    private $iconService;

     /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "dataListService" = @DI\Inject("sl_core.data_list"),
     *     "jstreeService" = @DI\Inject("sl_core.js_tree"),
     *     "iconService" = @DI\Inject("sl_core.icon"),
     * })
     */
    public function __construct(EntityManager $em, DataListService $dataListService, JSTreeService $jstreeService, IconService $iconService)
    {
        $this->em = $em;
        $this->dataListService = $dataListService;
        $this->jstreeService = $jstreeService;
        $this->iconService = $iconService;
    }

    /**
     * Display DataList main screen
     *
     */
    public function indexAction(Request $request)
    {   
        if ($request->isXmlHttpRequest()) {
            $response = $this->render('SLCoreBundle:DataList:index.html.twig');
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }

        return $response; 
    }

    /**
    * Display form to create DataList.
    *
    */
    public function newAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {

            $dataList = new DataList();

            $form   = $this->createCreateForm($dataList);

            $response = $this->render('SLCoreBundle::save.html.twig', array(
                'entity' => $dataList,
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
     */
    public function createAction(Request $request)
    {
        $dataList = new DataList();

        $form = $this->createCreateForm($dataList);

        $form->handleRequest($request);

        if ($request->isXmlHttpRequest()) {
            
            $isValid = $form->isValid();
            if ($isValid) {

                //Define DataList display position
                $maxDiplayOrder = $this->em->getRepository('SLCoreBundle:DataList')->findMaxDisplayOrder();
                $dataList->setDisplayOrder($maxDiplayOrder + 1); 

                //Save DataList in database
                $this->em->persist($dataList);
                $this->em->flush();

                //Dont delete this flush : Persist data after Doctrine evenement
                $this->em->flush();
            }  
            
            $jsonResponse = $this->dataListService->createJsonResponse($dataList, $form); 

            $response = $jsonResponse;
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }

        return $response; 
    }


    /**
    * Create DataList form
    *
    * @param DataList $dataList DataList to create
    *
    * @return Form $form Create form
    */
    private function createCreateForm(DataList $dataList)
    {       
        $form = $this->createForm(new DataListType(), $dataList, array(
            'action' => $this->generateUrl('data_list_create'),
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
    * Display form to edit DataList
    *
    * @param DataList $dataList DataList to edit
    *
    */
    public function editAction(DataList $dataList)
    {
        $form = $this->createEditForm($dataList);
 
        return $this->render('SLCoreBundle::save.html.twig', array(
            'entity' => $dataList,
            'form'   => $form->createView(),
            )
        );
    }

    /**
    * Update form action
    *
    * @param DataList $dataList DataList to edit
    *
    * @ParamConverter("dataList", options={"repository_method" = "findFullById"})
    */
    public function updateAction(Request $request, DataList $dataList)
    {
        $form = $this->createEditForm($dataList);

        $form->handleRequest($request);

        if ($request->isXmlHttpRequest()) {

            $isValid = $form->isValid();
            if ($isValid) {

                $this->em->flush();

                $html = $this->renderView('SLCoreBundle:DataList:show.html.twig', array(
                    'dataList' => $dataList, 
                    )
                );
                $nodeStructure = $this->jstreeService->updateDataListNode($dataList);
            }
            else {
                //Create form with errors
                $html = $this->renderView('SLCoreBundle:DataList:save.html.twig', array(
                    'action' => 'update',
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
    * Update DataList form
    *
    * @param DataList $dataList DataList to update
    *
    * @return Form $form Update form
    */
    private function createEditForm(DataList $dataList)
    {     
        $form = $this->createForm(new DataListType(), $dataList, array(
            'action' => $this->generateUrl('data_list_update', array('id' => $dataList->getId())),
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
     * Show a DataList
     *
     * @param DataList $dataList DataList to show
     *
     * @ParamConverter("dataList", options={"repository_method" = "findFullById"})
     */
    public function showAction(Request $request, DataList $dataList)
    {
        if ($request->isXmlHttpRequest()) {

            $response = $this->render('SLCoreBundle:DataList:show.html.twig', array(
                'dataList' => $dataList, 
                )
            );
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }

        return $response; 
    }

    /**
    * Display form to remove DataList.
    *
    * @param DataList $dataList DataList to remove
    *
    */
    public function removeAction(DataList $dataList)
    {
        //DataList integrity control before delete
        $integrityError = $this->dataListService->integrityControlBeforeDelete($dataList); 
        if($integrityError == null) {
              
            $form = $this->createDeleteForm($dataList);

            $response = $this->render('SLCoreBundle::save.html.twig', array(
                'entity' => $dataList,
                'form'   => $form->createView(),
                )
            );
        }
        else {

            //Create error modal window
            $response = $this->render('SLCoreBundle::errorModal.html.twig', array(
                'title' => $integrityError['title'],
                'message'   => $integrityError['message'],
                )
            );
        }

        return $response;
    }

    /**
     * Delete form action.
     *
     * @param DataList $dataList DataList to delete
     *
     */
    public function deleteAction(Request $request, DataList $dataList)
    {
        if ($request->isXmlHttpRequest()) {

            $nodeStructure = array(
                'id' => $dataList->getTechnicalName(),
            );
   
            $form = $this->createDeleteForm($dataList);

            $this->em->remove($dataList);

            $data = array(  
                'form' => array(
                    'action' => strtolower($form->getConfig()->getMethod()),
                    'isValid' => true,
                    ),
                'html' => null,
                'node' => array(
                    'nodeStructure' => $nodeStructure,
                    'nodeProperties' => null,
                ),
            );
            $response = new JsonResponse($data);

            $this->em->flush();
           
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }   

        return $response;    
    }

    /**
     * Delete DataList Form
     *
     * @param DataList $dataList DataList to delete
     *
     * @return Form $form Delete form
     */
    private function createDeleteForm(DataList $dataList)
    {
        $method = 'DELETE'; 

        $form = $this->createForm(new DataListType($method), $dataList, array(
            'action' => $this->generateUrl('data_list_delete', array('id' => $dataList->getId())),
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
     * Update DataList checkbox.
     *
     * @param DataList $dataList DataList to update
     *
     */
    public function updateCheckboxAction(Request $request, DataList $dataList)
    {
        if ($request->isXmlHttpRequest()) {

            $value = ($request->request->get('value')=='true')?true:false;

            $dataList->setEnabled($value);     
 
            $this->em->flush();

            $response = new JsonResponse(
                array(
                    'id' => $dataList->getTechnicalName(),
                    'icon' => $this->iconService->getDataListIcon($dataList),
                    )
                )
            ;
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }   

        return $response;    
    }
}
