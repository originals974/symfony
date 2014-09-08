<?php
namespace SL\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;

use SL\CoreBundle\Services\ElasticaService;
use SL\CoreBundle\Services\EntityService;

/**
 * Search controller.
 *
 */
class SearchController extends Controller
{
    private $em;
    private $elasticaService;
    private $entityService; 
    private $numberOfSearchResults; 

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "elasticaService" = @DI\Inject("sl_core.elastica"),
     *     "entityService" = @DI\Inject("sl_core.entity"),
     *     "numberOfSearchResults" = @DI\Inject("%sl_core.number_of_search_results%")
     * })
     */
    public function __construct(EntityManager $em, ElasticaService $elasticaService, EntityService $entityService, $numberOfSearchResults)
    {
        $this->em = $em;
        $this->elasticaService = $elasticaService;
        $this->entityService = $entityService; 
        $this->numberOfSearchResults = $numberOfSearchResults; 
    }

    /**
    * Display search results panel
    *
    * @param Request $request
    *
    * @return Mixed $response
    */
    public function searchAction(Request $request) {
       
        if ($request->isXmlHttpRequest()) {    

            $form = $this->entityService->createSearchForm();
            $form->handleRequest($request);
            
            $searchPattern = $form->get('searchField')->getData();

            //Get all active entityClasses
            $filters = $this->em->getFilters();
            $filters->disable('softdeleteable');
            
            $entityClasses = $this->em->getRepository('SLCoreBundle:EntityClass\EntityClass')->fullFindAll();
                       
            $filters->enable('softdeleteable');

            $html = $this->renderView('SLCoreBundle:Entity:searchResults.html.twig', array(
                    'entityClasses' => $entityClasses,
                    )
                );

            $data = array(
                'isValid' => true,
                'content' => $html
                ); 

            $response = new JsonResponse($data);
        }
        else {

            $response = $this->redirect($this->generateUrl('front_end'));
        }

        return $response; 
    }

    /**
    * Refresh JsTree Results 
    * according to $pattern
    * for entity with $entityClassTechnicalName class name
    *
    * @param Request $request
    * @param string $pattern
    * @param string $entityClassTechnicalName
    *
    * @return Mixed $response
    */
    public function refreshJsTreeSearchResultsAction(Request $request, $pattern, $entityClassTechnicalName)
    {
        if ($request->isXmlHttpRequest()) {    

            $data = array(); 

            $entities = $this->getSearchResults($pattern, $entityClassTechnicalName);            
            $this->elasticaService->entitiesToJSTreeData($data, $entities);
            
            $response = new JsonResponse($data);
        }
        else {

            $response = $this->redirect($this->generateUrl('front_end'));
        }

        return $response; 
    }

    /**
    * Get search results 
    * according to $pattern
    * for entity with $entityClassTechnicalName class name
    *
    * @param string $pattern 
    * @param string $entityClassTechnicalName
    *
    * @return array $entities
    */
    private function getSearchResults($pattern, $entityClassTechnicalName){

        $finderName = 'fos_elastica.finder.slcore.'.$entityClassTechnicalName; 
        $finder = $this->get($finderName); 

        $filters = $this->em->getFilters();
        $filters->disable('softdeleteable');

        $this->get('fos_elastica.index.slcore')->refresh(); 
        $entities = $finder->find($pattern, $this->numberOfSearchResults);

        $filters->enable('softdeleteable');

        return $entities; 
    }
}
