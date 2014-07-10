<?php

namespace SL\CoreBundle\Services;

//Symfony classes
use Doctrine\ORM\EntityManager;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Form\Form; 
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine;  

//Custom classes
use SL\CoreBundle\Entity\DataList;

/**
 * DataList Service
 *
 */
class DataListService
{
    private $em;
    private $translator;
    private $jstreeService;
    private $templating;

    /**
     * Constructor
     *
     * @param EntityManager $em
     * @param Translator $translator
     * @param JSTreeService $jstreeService
     * @param TimedTwigEngine $templating
     *
     */
    public function __construct(EntityManager $em, Translator $translator, JSTreeService $jstreeService, TimedTwigEngine $templating)
    {
        $this->em = $em;
        $this->translator = $translator;
        $this->jstreeService = $jstreeService;
        $this->templating = $templating;
    }

   /**
     * Verify integrity of a DataList before delete
     *
     * @param DataList $dataList DataList to delete
     *
     * @return Array $integrityError Title and error message
     */
    public function integrityControlBeforeDelete(DataList $dataList) 
    {
        $integrityError = null;

        //Check if DataList is not a Property of an Object
        $property = $this->em->getRepository('SLCoreBundle:ListProperty')->findByDataList($dataList);

        if($property != null){
            $title = $this->translator->trans('delete.error.title');
            $message = $this->translator->trans('delete.dataList.reference.error.message');

            $integrityError = array(
                'title' => $title,
                'message' => $message,
                );

            return $integrityError;
        }

        return $integrityError; 
    }

    /**
     * Create JsonResponse for DataList creation  
     *
     * @param DataList $dataList Created DataList
     * @param Form $form Creation DataList form
     *
     * @return JsonResponse
     */
    public function createJsonResponse(DataList $dataList, Form $form) {

        $isValid = $form->isValid(); 

        if($isValid) {

            $html = null; 
            $nodeStructure = $this->jstreeService->createNewDataListNode($dataList);
            $nodeProperties = array(
                'parent' => 'current.node',
                'select' => true,  
            );
        }
        else {
            //Create form with errors
            $html = $this->templating->render('SLCoreBundle::save.html.twig', array(
                'entity' => $dataList,
                'form'   => $form->createView(),
                )
            ); 
            $nodeStructure = null; 
            $nodeProperties = null;
        }

        $data = array(
            'form' => array(
                'action' => strtolower($form->getConfig()->getMethod()),
                'isValid' => $isValid,
                ),
            'html' => $html,
            'node' => array(
                'nodeStructure' => $nodeStructure,
                'nodeProperties' => $nodeProperties,
            ),
        );

        return new JsonResponse($data); 
    }
}
