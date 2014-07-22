<?php
namespace SL\CoreBundle\Controller;

//Symfony classes
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\ElasticaBundle\Elastica\Index;
use JMS\DiExtraBundle\Annotation as DI;

//Custom classes
use SL\CoreBundle\Entity\Search;
use SL\CoreBundle\Form\SearchType;
use SL\CoreBundle\Services\ElasticaService;

/**
 * Search controller.
 *
 */
class SearchController extends Controller
{

    private $elasticaService;
    private $type;

    /**
     * @DI\InjectParams({
     *     "elasticaService" = @DI\Inject("sl_core.elastica"),
     *     "type" = @DI\Inject("fos_elastica.index.slcore")
     * })
     */
    public function __construct(ElasticaService $elasticaService, Index $type)
    {
        $this->elasticaService = $elasticaService;
        $this->type = $type; 
    }

    /**
    * Search entities in elastica index
    */
    public function processAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {    

            $pattern = $request->query->get('pattern');

            $data = array(); 

            if($pattern != "") {

                $elasticaResultsSet = $this->type->search($pattern);

                $elasticaResults = $elasticaResultsSet->getResults();

                foreach ($elasticaResults as $elasticaResult) {
                    $elasticaResultArray = $elasticaResult->getData(); 
                    $this->elasticaService->elasticSearchToJSTree($elasticaResultArray);
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
