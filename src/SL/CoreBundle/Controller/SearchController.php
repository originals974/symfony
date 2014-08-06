<?php
namespace SL\CoreBundle\Controller;

//Symfony classes
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;

//Custom classes
use SL\CoreBundle\Entity\Search;
use SL\CoreBundle\Services\ElasticaService;
use SL\CoreBundle\Services\SearchService;

/**
 * Search controller.
 *
 */
class SearchController extends Controller
{
    private $em;
    private $elasticaService;
    private $searchService; 

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "elasticaService" = @DI\Inject("sl_core.elastica"),
     *     "searchService" = @DI\Inject("sl_core.search")
     * })
     */
    public function __construct(EntityManager $em, ElasticaService $elasticaService, SearchService $searchService)
    {
        $this->em = $em;
        $this->elasticaService = $elasticaService;
        $this->searchService = $searchService; 
    }

    /**
    * Display search results
    */
    public function searchAction(Request $request) {
       
        $search =  new Search(); 
        
        $form = $this->searchService->createSearchForm($search);
        $form->handleRequest($request);

        if ($request->isXmlHttpRequest()) {    

            //Get all active objects
            $filters = $this->em->getFilters();
            $filters->disable('softdeleteable');
            
            $objects = $this->em->getRepository('SLCoreBundle:Object')->fullFindAll();
            
            $filters->enable('softdeleteable');
            
            //Get number of results for each object
            $objectsArray = array(); 
            foreach($objects as $object){

                //Search in Elactica index
                $entities = $this->getSearchResults($search->getSearchField(), $object->getTechnicalName(), 100);

                //Include object only if it has results
                if(!empty($entities)){
                    $objectArray = array(
                        'object' => $object,
                        'nb_results' => count($entities), 
                        );
                    $objectsArray[] = $objectArray;
                }
            }
            $html = $this->renderView('SLCoreBundle:Front:searchResults.html.twig', array(
                    'objectsArray' => $objectsArray,
                    )
                );

            $data = array(
                'isValid' => true,
                'content' => $html
                ); 

            $response = new JsonResponse($data);
        }
        else {

            //Redirect to index page
            $response = $this->redirect($this->generateUrl('front'));
        }

        return $response; 
    }

    /**
    * Refresh JsTree Results for an object
    *
    * @param String $pattern Search pattern
    * @param String $objectTechnicalName
    */
    public function refreshJsTreeSearchResultsAction(Request $request, $pattern, $objectTechnicalName)
    {
        if ($request->isXmlHttpRequest()) {    

            $data = array(); 

            $filters = $this->em->getFilters();
            $filters->disable('softdeleteable');

            $entities = $this->getSearchResults($pattern, $objectTechnicalName, 50);
                    
            $data = array();             
            $this->elasticaService->entitiesToJSTreeData($data, $entities);
            
            $filters->enable('softdeleteable');
            
            $response = new JsonResponse($data);
        }
        else {

            //Redirect to index page
            $response = $this->redirect($this->generateUrl('front'));
        }

        return $response; 
    }

    /**
    * Get search results for an object 
    *
    * @param String $pattern Search pattern
    * @param String $objectTechnicalName
    * @param Integer $limit Max results number 
    *
    * @return array $entities Array of results
    */
    private function getSearchResults($pattern, $objectTechnicalName, $limit){

        $finderName = 'fos_elastica.finder.slcore.'.$objectTechnicalName; 
        $finder = $this->get($finderName); 
        $entities = $finder->find($pattern, $limit);

        return $entities; 
    }
}
