<?php

namespace SL\CoreBundle\Services;

//Symfony classes
use Symfony\Component\Form\Form; 
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine;  

//Custom classes
use SL\CoreBundle\Entity\DataListValue;

/**
 * DataListValue Service
 *
 */
class DataListValueService
{
    private $jstreeService;
    private $templating;

    /**
     * Constructor
     *
     * @param JSTreeService $jstreeService
     * @param TimedTwigEngine $templating
     *
     */
    public function __construct(JSTreeService $jstreeService, TimedTwigEngine $templating)
    {
        $this->jstreeService = $jstreeService;
        $this->templating = $templating;
    }

    /**
     * Create JsonResponse for DataListValue creation  
     *
     * @param DataListValue $dataListValue Created DataListValue
     * @param Form $form Creation DataListValue form
     *
     * @return JsonResponse
     */
    public function createJsonResponse(DataListValue $dataListValue, Form $form) {

        $isValid = $form->isValid(); 

        if($isValid) {

            $html = $this->templating->render('SLCoreBundle:DataListValue:dataListValueTable.html.twig', array(
                'dataList' => $dataListValue->getDataList(), 
                )
            );

            $nodeStructure = $this->jstreeService->createNewDataListValueNode($dataListValue);
            $nodeProperties = array(
                'parent' => 'current.node',
                'select' => false,  
            );
        }
        else {
            //Create form with errors 
            $html = $this->templating->render('SLCoreBundle::save.html.twig', array(
                'entity' => $dataListValue,
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
