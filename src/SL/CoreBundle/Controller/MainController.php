<?php

namespace SL\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\DiExtraBundle\Annotation as DI;
use Doctrine\ORM\EntityManager;

use SL\CoreBundle\Services\EntityService;
use SL\CoreBundle\Services\ElasticaService;
use SL\CoreBundle\Services\DoctrineService;

/**
 * Main controller.
 *
 */
class MainController extends Controller
{
    private $em;
    private $entityService;
    private $elasticaService;
    private $doctrineService;

     /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "entityService" = @DI\Inject("sl_core.entity"),
     *     "elasticaService" = @DI\Inject("sl_core.elastica"),
     *     "doctrineService" = @DI\Inject("sl_core.doctrine"),
     * })
     */
    public function __construct(EntityManager $em, EntityService $entityService, ElasticaService $elasticaService, DoctrineService $doctrineService)
    {
        $this->em = $em;
        $this->entityService = $entityService;
        $this->elasticaService = $elasticaService;
        $this->doctrineService = $doctrineService;
    }


    /**
     * Init elastica config file
     *
     * @param Request $request
     *
     * @return Response $response
     */
    public function initAction(Request $request)
    {
        //Load init fixtures
        $this->doctrineService->loadInitFixture();
        
        //Generate elastica config file
        $this->elasticaService->updateElasticaConfigFile(1,100); 

        //Generate Document entity file and database structure
        $documentEntityClass = $this->em->getRepository('SLCoreBundle:EntityClass\EntityClass')->findOneByIsDocument(true);
        $this->doctrineService->generateEntityFileAndObjectSchema($documentEntityClass);

        return new Response(); 
    }

    /**
     * Open front end main page
     *
     * @param Request $request
     *
     * @return Response $response
     */
	public function indexFrontEndAction()
    {
        $form = $this->entityService->createSearchForm();

        return $this->render('SLCoreBundle:FrontEnd:index.html.twig', array(
            'form'   => $form->createView(),
            )
        );
    }

    /**
     * Open back end main page
     *
     * @param Request $request
     *
     * @return Response $response
     */
    public function indexBackEndAction(Request $request)
    {
        return $this->render('SLCoreBundle:BackEnd:index.html.twig');
    }
}
