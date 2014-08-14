<?php

namespace SL\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\DiExtraBundle\Annotation as DI;

use SL\CoreBundle\Services\EntityService;
use SL\CoreBundle\Services\ElasticaService;

/**
 * Main controller.
 *
 */
class MainController extends Controller
{
    private $entityService;
    private $elasticaService;

     /**
     * @DI\InjectParams({
     *     "entityService" = @DI\Inject("sl_core.entity"),
     *     "elasticaService" = @DI\Inject("sl_core.elastica"),
     * })
     */
    public function __construct(EntityService $entityService, ElasticaService $elasticaService)
    {
        $this->entityService = $entityService;
         $this->elasticaService = $elasticaService;
    }


    /**
     * Init elastica config file
     *
     * @param Request $request
     *
     * @return Response $response
     */
    public function initAction(Request $request)
    {
        $this->elasticaService->updateElasticaConfigFile(1,1000); 

        return new Response(); 
    }

    /**
     * Open front end main page
     *
     * @param Request $request
     *
     * @return Response $response
     */
	public function indexFrontEndAction()
    {
        $form = $this->entityService->createSearchForm();

        return $this->render('SLCoreBundle:FrontEnd:index.html.twig', array(
            'form'   => $form->createView(),
            )
        );
    }

    /**
     * Open back end main page
     *
     * @param Request $request
     *
     * @return Response $response
     */
    public function indexBackEndAction(Request $request)
    {
        return $this->render('SLCoreBundle:BackEnd:index.html.twig');
    }
}
