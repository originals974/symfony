<?php
//TO COMPLETE
namespace SL\CoreBundle\Controller;

//Symfony classes
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
   
    public function processAction(Request $request)
    {
        $search = new Search(); 

        $form = $this->createForm(new SearchType(), $search, array(
            'action' => $this->generateUrl('search_process'),
            )
        );

        $form->handleRequest($request);

        if ($request->isXmlHttpRequest()) {
            
            //Ajax response construction
            $elasticaService = $this->get('sl_core.elastica');

            $objectType = $this->get('fos_elastica.index.slcore');
            $elasticaResultsSet = $objectType->search($search->getSearchField());

            $elasticaResults  = $elasticaResultsSet->getResults();

            $data = array(); 

            foreach ($elasticaResults as $elasticaResult) {
                $elasticaResultArray = $elasticaResult->getData();
                $elasticaService->elasticSearchToJSTree($elasticaResultArray);
                array_push($data, $elasticaResultArray);
            }

            //Create the Json Response array
            $data = array(  
                'mode' => 'search',
                'jsdata' => $data,
                'isValid' => true,
            );

            $response = new JsonResponse($data);
        }
        else {

            //Redirect to index page
            $response = $this->redirect($this->generateUrl('search'));
        }

        return $response; 
    }
}
