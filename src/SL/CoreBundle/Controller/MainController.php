<?php

namespace SL\CoreBundle\Controller;

//Symfony classes
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Front controller.
 *
 */
class MainController extends Controller
{
    /**
     * Open front end main page
     */
	public function indexFrontEndAction()
    {
        return $this->render('SLCoreBundle:FrontEnd:index.html.twig');
    }

    /**
     * Open back end main page
     */
    public function indexBackEndAction(Request $request)
    {
        return $this->render('SLCoreBundle:BackEnd:index.html.twig');
    }
}
