<?php

namespace SL\CoreBundle\Controller;

//Symfony classes
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

//Custom classes
use SL\CoreBundle\Entity\Search;
use SL\CoreBundle\Form\SearchType;
use SL\CoreBundle\Entity\Technicien;

/**
 * Front controller.
 *
 */
class MainController extends Controller
{

    /**
     * Test
     */
    public function testAction(Request $request)
    {
        return new Response(var_dump('coucou')); 
    }


    /**
     * Init application
     */
    public function initAction(Request $request)
    {
        $this->get('sl_core.elastica')->updateElasticaConfigFile(1,1000); 

        return new Response(); 
    }

    /**
     * Open front end main page
     */
	public function indexFrontEndAction()
    {
        $search = new Search(); 

        $form = $this->createForm(new SearchType(), $search);

        return $this->render('SLCoreBundle:FrontEnd:index.html.twig', array(
            'form'   => $form->createView(),
            )
        );
    }

    /**
     * Open back end main page
     */
    public function indexBackEndAction(Request $request)
    {
        return $this->render('SLCoreBundle:BackEnd:index.html.twig');
    }
}
