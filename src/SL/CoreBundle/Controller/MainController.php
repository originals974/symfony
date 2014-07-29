<?php

namespace SL\CoreBundle\Controller;

//Symfony classes
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\DiExtraBundle\Annotation as DI;

//Custom classes
use SL\CoreBundle\Entity\Search;
use SL\CoreBundle\Services\SearchService;

/**
 * Front controller.
 *
 */
class MainController extends Controller
{
    private $searchService;

     /**
     * @DI\InjectParams({
     *     "searchService" = @DI\Inject("sl_core.search"),
     * })
     */
    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * Test
     */
    public function testAction(Request $request)
    {
        return new Response(var_dump('coucou')); 
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
        $search = new Search(); 

        $form = $this->searchService->createSearchForm($search);

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
