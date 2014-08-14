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

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "elasticaService" = @DI\Inject("sl_core.elastica"),
     *     "entityService" = @DI\Inject("sl_core.entity")
     * })
     */
    public function __construct(EntityManager $em, ElasticaService $elasticaService, EntityService $entityService)
    {
        $this->em = $em;
        $this->elasticaService = $elasticaService;
        $this->entityService = $entityService; 
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
            
            //Get number of results for each entityClass
            $entityClassesArray = array(); 
            foreach($entityClasses as $entityClass){

                $entities = $this->getSearchResults($searchPattern, $entityClass->getTechnicalName(), 100);

                //Include entityClass only if it has results
                if(!empty($entities)){
                    $entityClassArray = array(
                        'entity_class' => $entityClass,
                        'nb_results' => count($entities), 
                        );
                    $entityClassesArray[] = $entityClassArray;
                }
            }
            $html = $this->renderView('SLCoreBundle:Entity:searchResults.html.twig', array(
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

            $limit = $this->container->getParameter('limit_number_of_search_results');
            $entities = $this->getSearchResults($pattern, $entityClassTechnicalName, 50);            
            $this->elasticaService->entitiesToJSTreeData($data, $entities);
            
            $response = new JsonResponse($data);
        }
        else {

            $response = $this->redirect($this->generateUrl('front_end'));
        }

        return $response; 
    }

    /**
    * Get last $limit search results 
    * according to $pattern
    * for entity with $entityClassTechnicalName class name
    *
    * @param string $pattern 
    * @param string $entityClassTechnicalName
    * @param integer $limit|50 
    *
    * @return array $entities
    */
    private function getSearchResults($pattern, $entityClassTechnicalName, $limit = 50){

        $finderName = 'fos_elastica.finder.slcore.'.$entityClassTechnicalName; 
        $finder = $this->get($finderName); 

        $filters = $this->em->getFilters();
        $filters->disable('softdeleteable');

        $entities = $finder->find($pattern, $limit);

        $filters->enable('softdeleteable');

        return $entities; 
    }
}
