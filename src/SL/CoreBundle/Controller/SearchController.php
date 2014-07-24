<?php
namespace SL\CoreBundle\Controller;

//Symfony classes
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\ElasticaBundle\Elastica\Index;
use Doctrine\ORM\EntityManager;
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
    private $em;
    private $elasticaService;
    private $type;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "elasticaService" = @DI\Inject("sl_core.elastica"),
     *     "type" = @DI\Inject("fos_elastica.index.slcore")
     * })
     */
    public function __construct(EntityManager $em, ElasticaService $elasticaService, Index $type)
    {
        $this->em = $em;
        $this->elasticaService = $elasticaService;
        $this->type = $type; 
    }

    /**
    * Display search results
    */
    public function searchAction(Request $request) {
       
        if ($request->isXmlHttpRequest()) {    

            $objects = $this->em->getRepository('SLCoreBundle:Object')->findAllEnabledObjects();
            $documents = $this->em->getRepository('SLCoreBundle:Object')->findAllEnabledDocuments();
            $objects = array_merge($objects, $documents);

            $html = $this->renderView('SLCoreBundle:Front:searchResults.html.twig', array(
                    'objects' => $objects,
                    )
                ); 

            //Format object technical name array for javascript
            $objectsTechnicalName = array(); 
            foreach($objects as $object){
                array_push($objectsTechnicalName, $object->getTechnicalName()); 
            }

            $data = array(
                'html' => $html,
                'objectsTechnicalName' => $objectsTechnicalName,
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
    * Create JsTree Results for an object
    */
    public function createJsTreeSearchResultsAction(Request $request, $pattern, $objectTechnicalName)
    {
        if ($request->isXmlHttpRequest()) {    

            $data = array(); 

            if($pattern != "") {
                $finderName = 'fos_elastica.finder.slcore.'.$objectTechnicalName; 

                $finder = $this->get($finderName); 

                $entities = $finder->find($pattern, 50);
                    
                $data = array();             
                $this->elasticaService->EntitiesToJSTreeData($data, $entities);

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
