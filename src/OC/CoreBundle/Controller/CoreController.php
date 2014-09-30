<?php

namespace OC\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class CoreController extends Controller
{

    public function indexAction()
    {
        return $this->render('OCCoreBundle:Default:index.html.twig');
    }

    public function contactAction(Request $request)
    {
        $request->getSession()->getFlashBag()->add('info',
                'La page de contact n\'est pas encore disponible, merci de revenir plus tard.');
        return $this->redirect($this->generateUrl('oc_core_homepage'));
    }
}