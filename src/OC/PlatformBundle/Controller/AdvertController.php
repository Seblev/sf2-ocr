<?php

namespace OC\PlatformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class AdvertController extends Controller
{

    public function indexAction()
    {
        //Avec un template twig
        $content = $this->get('templating')->render('OCPlatformBundle:Advert:index.html.twig',
                array(
            'nom' => 'Lily'
                )
        );
        return new Response($content);
    }

    public function byeAction()
    {
        //Sans template
        return new Response("Bye bye World !");
    }
}