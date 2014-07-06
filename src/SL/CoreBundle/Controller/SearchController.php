<?php
//TO COMPLETE
namespace SL\CoreBundle\Controller;

//Symfony classes
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

//Custom classes
use SL\CoreBundle\Entity\Search;
use SL\CoreBundle\Form\SearchType;

/**
 * Search controller.
 *
 */
class SearchController extends Controller
{
    /**
     * Show search page
     */
	public function indexAction()
    {
        //Variables initialisation
        $search = new Search(); 

        //Form creation
        $form = $this->createSearchForm($search);

        //View creation
        return $this->render('SLCoreBundle:Search:index.html.twig', array(
            'form'   => $form->createView(),
            )
        );
    }

    public function processAction(Request $request)
    {
        //Variables initialisation
        $search = new Search();
        
        //Form creation
        $form = $this->createSearchForm($search);

        //Associate return form data with entity object
        $form->handleRequest($request);

        if ($request->isXmlHttpRequest()) {
            
            //Ajax response construction
            $elasticaService = $this->get('sl_core.elastica');
            $elasticaResultsSet = $elasticaService->elasticaRequest($search->getSearchField()); 

            $elasticaResults  = $elasticaResultsSet->getResults();
            $totalResults     = $elasticaResultsSet->getTotalHits();

            $data = array(); 

            foreach ($elasticaResults as $elasticaResult) {
                $elasticaResultArray = $elasticaResult->getData();
                $elasticaService->elasticSearchToBootstrapTree($elasticaResultArray);
                array_push($data, $elasticaResultArray);
            }

            $response = new JsonResponse($data);
        }
        else {

            //Redirect to index page
            $response = $this->redirect($this->generateUrl('search'));
        }

        return $response; 
    }

    private function createSearchForm(Search $search) 
    {
        //Form creation        
        $form = $this->createForm(new SearchType(), $search, array(
            'action' => $this->generateUrl('search_process'),
            )
        );

        //Submit button creation        
        $form->add('submit', 'submit', array(
                'label' => 'search',
                'attr' => array('class'=>'btn btn-primary btn-sm'),
                )
            )
        ;

        return $form;
    }
}
