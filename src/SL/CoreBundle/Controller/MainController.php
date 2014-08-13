<?php

namespace SL\CoreBundle\Controller;

//Symfony classes
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\DiExtraBundle\Annotation as DI;

//Custom classes
use SL\CoreBundle\Entity\Search;
use SL\CoreBundle\Services\FrontService;

/**
 * Front controller.
 *
 */
class MainController extends Controller
{
    private $frontService;

     /**
     * @DI\InjectParams({
     *     "frontService" = @DI\Inject("sl_core.front"),
     * })
     */
    public function __construct(FrontService $frontService)
    {
        $this->frontService = $frontService;
    }


    /**
     * Init application cutom file
     */
    public function initAction(Request $request)
    {
        //Update app/config/elastica.yml file
        $this->get('sl_core.elastica')->updateElasticaConfigFile(1,1000); 

        return new Response(); 
    }

    /**
     * Open front end main page
     */
	public function indexFrontEndAction()
    {
        $form = $this->frontService->createSearchForm();

        return $this->render('SLCoreBundle:FrontEnd:index.html.twig', array(
            'form'   => $form->createView(),
            )
        );
    }

    /**
     * Open back end main page
     */
    public function indexBackEndAction(Request $request)
    {
        return $this->render('SLCoreBundle:BackEnd:index.html.twig');
    }
}
