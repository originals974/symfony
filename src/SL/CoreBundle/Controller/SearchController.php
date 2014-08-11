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

            //Get all active entityClasses
            $filters = $this->em->getFilters();
            $filters->disable('softdeleteable');
            
            $entityClasses = $this->em->getRepository('SLCoreBundle:EntityClass\EntityClass')->fullFindAll();
            
            $filters->enable('softdeleteable');
            
            //Get number of results for each entityClass
            $entityClassesArray = array(); 
            foreach($entityClasses as $entityClass){

                //Search in Elactica index
                $entities = $this->getSearchResults($search->getSearchField(), $entityClass->getTechnicalName(), 100);

                //Include entityClass only if it has results
                if(!empty($entities)){
                    $entityClassArray = array(
                        'entityClass' => $entityClass,
                        'nb_results' => count($entities), 
                        );
                    $entityClassesArray[] = $entityClassArray;
                }
            }
            $html = $this->renderView('SLCoreBundle:Front:searchResults.html.twig', array(
                    'entityClassesArray' => $entityClassesArray,
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
    * Refresh JsTree Results for an entityClass
    *
    * @param String $pattern Search pattern
    * @param String $entityClassTechnicalName
    */
    public function refreshJsTreeSearchResultsAction(Request $request, $pattern, $entityClassTechnicalName)
    {
        if ($request->isXmlHttpRequest()) {    

            $data = array(); 

            $filters = $this->em->getFilters();
            $filters->disable('softdeleteable');

            $entities = $this->getSearchResults($pattern, $entityClassTechnicalName, 50);
                    
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
    * Get search results for an entityClass 
    *
    * @param String $pattern Search pattern
    * @param String $entityClassTechnicalName
    * @param Integer $limit Max results number 
    *
    * @return array $entities Array of results
    */
    private function getSearchResults($pattern, $entityClassTechnicalName, $limit){

        $finderName = 'fos_elastica.finder.slcore.'.$entityClassTechnicalName; 
        $finder = $this->get($finderName); 
        $entities = $finder->find($pattern, $limit);

        return $entities; 
    }
}
