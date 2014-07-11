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
        /*$repo = $this->getDoctrine()->getEntityManager()->getRepository('SLCoreBundle:Object');
        $this->getDoctrine()->getEntityManager()->clear(); 


        $object = $repo->find(24);
        $path = $repo->getPath($object);
        
        foreach($path as $object) {
            var_dump($object->getDisplayName()); 
        }*/

        $em = $this->getDoctrine()->getEntityManager();

        /*$tech = new Technicien(); 
        $tech->setSpe('Informatique'); 
        $tech->setQualif('Niveau 2');
        $tech->setName('Sam');*/

        //$tech = $em->getRepository('SLCoreBundle:Employee')->find(1);

        //$em->persist($tech);
        //$em->flush();
        $object = $em->getRepository('SLCoreBundle:Object')->find(51);
        $children = $em->getRepository('SLCoreBundle:Object')->children($object, false); 

        foreach($children as $object) {
            var_dump($object->getDisplayName()); 
        }

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

        $form = $this->createForm(new SearchType(), $search, array(
            'action' => $this->generateUrl('search_process'),
            )
        );

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
