<?php

namespace SL\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Server controller
 *
 */
class ServerController extends Controller
{
    /**
     * Display general informations about server
     *
     * @param Request $request
     *
     * @return Response $response
     */
    public function indexAction(Request $request)
    {   
        if ($request->isXmlHttpRequest()) {
            $response = $this->render('SLCoreBundle:Server:index.html.twig');
        }
        else {

            $response = $this->redirect($this->generateUrl('back_end'));
        }

        return $response; 
    }
}
