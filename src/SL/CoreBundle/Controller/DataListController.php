<?php

namespace SL\CoreBundle\Controller;

//Symfony classes
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;

//Custom classes
use SL\CoreBundle\Entity\DataList;
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
     * Display datalist create screen
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
    * Display form to create datalist entity
    */
    public function newAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {

            $dataList = new DataList();

            $form = $this->createCreateForm($dataList);

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
     * Create datalist entity
     */
    public function createAction(Request $request)
    { 
        $dataList = new DataList();

        $form = $this->createCreateForm($dataList);
        $form->handleRequest($request);

        if ($request->isXmlHttpRequest()) {
            
            $isValid = $form->isValid();
            if ($isValid) {

                $this->em->persist($dataList);
                $this->em->flush();

                $html = null;
                $jsTree = $this->jstreeService->createNewDataListNode($dataList); 
            } 
            else {
                $jsTree = null; 
                $html = $this->renderView('SLCoreBundle::save.html.twig', array(
                    'entity' => $dataList,
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
    * Create datalist form
    *
    * @param DataList $dataList
    *
    * @return Form $form
    */
    private function createCreateForm(DataList $dataList)
    {   
        $form = $this->createForm('data_list', $dataList, array(
            'action' => $this->generateUrl('data_list_create'),
            'method' => 'POST',
            'submit_label' => 'create',
            'submit_color' => 'primary',
            )
        );

        return $form;
    }

    /**
    * Display form to edit datalist entity
    *
    * @param DataList $dataList 
    */
    public function editAction(Request $request, DataList $dataList)
    {
        if ($request->isXmlHttpRequest()) {

            $form = $this->createEditForm($dataList);
     
            $response = $this->render('SLCoreBundle::save.html.twig', array(
                'entity' => $dataList,
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
    * Update datalist entity
    *
    * @param DataList $dataList Datalist to update
    */
    public function updateAction(Request $request, DataList $dataList)
    {
        $form = $this->createEditForm($dataList);
        $form->handleRequest($request);

        if ($request->isXmlHttpRequest()) {

            $isValid = $form->isValid();
            if ($isValid) {

                $this->em->flush();

                $html = null; 
                $jsTree = $dataList->getDisplayName();
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
    * Update datalist form
    *
    * @param DataList $dataList
    *
    * @return Form $form
    */
    private function createEditForm(DataList $dataList)
    {     
        $form = $this->createForm('data_list', $dataList, array(
            'action' => $this->generateUrl('data_list_update', array(
                'id' => $dataList->getId(),
                )
            ),
            'method' => 'PUT',
            'submit_label' => 'update',
            'submit_color' => 'primary',
            )
        );

        return $form;
    }

     /**
     * Show datalist entity
     *
     * @param DataList $dataList Datalist to show
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
    * Display form to remove datalist entity
    *
    * @param DataList $dataList
    */
    public function removeAction(Request $request, DataList $dataList)
    {
        if ($request->isXmlHttpRequest()) {

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
     * Delete datalist entity
     *
     * @param DataList $dataList Datalist to delete
     *
     */
    public function deleteAction(Request $request, DataList $dataList)
    {
        if ($request->isXmlHttpRequest()) {

            $this->em->remove($dataList);
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
     * Delete datalist form
     *
     * @param DataList $dataList
     *
     * @return Form $form
     */
    private function createDeleteForm(DataList $dataList)
    {
        $form = $this->createForm('data_list', $dataList, array(
            'action' => $this->generateUrl('data_list_delete', array(
                'id' => $dataList->getId(),
                )
            ),
            'method' => 'DELETE',
            'submit_label' => 'delete',
            'submit_color' => 'danger',
            )
        );

        return $form;
    }

    /**
     * Update datalist checkbox
     *
     * @param DataList $dataList Datalist to update
     */
    public function updateCheckboxAction(Request $request, DataList $dataList)
    {
        if ($request->isXmlHttpRequest()) {

            $value = ($request->request->get('value')=='true')?true:false;

            $dataList->setEnabled($value);     
 
            $this->em->flush();

            $response = new JsonResponse(null);
        }
        else {
            $response = $this->redirect($this->generateUrl('back_end'));
        }   

        return $response;    
    }
}
