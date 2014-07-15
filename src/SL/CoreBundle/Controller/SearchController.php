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
        if ($request->isXmlHttpRequest()) {    

            $pattern = $request->query->get('pattern');

            $data = array(); 

            if($pattern != "") {
                //Ajax response construction
                $elasticaService = $this->get('sl_core.elastica');

                $objectType = $this->get('fos_elastica.index.slcore');
                $elasticaResultsSet = $objectType->search($pattern);

                $elasticaResults = $elasticaResultsSet->getResults();

                foreach ($elasticaResults as $elasticaResult) {
                    $elasticaResultArray = $elasticaResult->getData();
                    $elasticaService->elasticSearchToJSTree($elasticaResultArray);
                    array_push($data, $elasticaResultArray);
                }
            }

            $response = new JsonResponse($data);
        }
        else {

            //Redirect to index page
            $response = $this->redirect($this->generateUrl('front'));
        }

        return $response; 
    }
}
