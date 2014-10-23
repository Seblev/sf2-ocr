<?php

namespace OC\PlatformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Form\AdvertType;
use OC\PlatformBundle\Entity\Image;
use OC\PlatformBundle\Entity\Application;
use OC\PlatformBundle\Entity\Skill;
use OC\PlatformBundle\Entity\AdvertSkill;
use OC\PlatformBundle\Bigbrother\BigbrotherEvents;
use OC\PlatformBundle\Bigbrother\MessagePostEvent;

class AdvertController extends Controller
{

    public function indexAction($page)
    {
        $nbPerPage = 2;

        if ($page < 1)
        {
            throw new NotFoundHttpException('Page "' . $page . '" inexistante.');
        }
        $em = $this
                ->getDoctrine()
                ->getManager()
        ;

        $listAdverts = $em
                ->getRepository('OCPlatformBundle:Advert')
                ->getAdverts($page, $nbPerPage)
        ;
        // ceil arrondi par excès
        $nbPages = ceil(count($listAdverts) / $nbPerPage);

        if ($page > $nbPages)
        {
            throw $this->createNotFoundException("La page " . $page . " n'existe pas.");
        }

        return $this->render('OCPlatformBundle:Advert:index.html.twig',
                        array(
                    'listAdverts' => $listAdverts,
                    'nbPages' => $nbPages,
                    'page' => $page
        ));
    }

    public function viewAction($id)
    {
        $em = $this
                ->getDoctrine()
                ->getManager()
        ;

        $advert = $em
                ->getRepository('OCPlatformBundle:Advert')
                ->find($id)
        ;

        if (null === $advert)
        {
            throw new NotFoundHttpException("L'annonce d'id " . $id . " n'existe pas.");
        }

        $listApplications = $em
                ->getRepository('OCPlatformBundle:Application')
                ->findBy(array('advert' => $advert))
        ;

        $listAdvertSkills = $em
                ->getRepository('OCPlatformBundle:AdvertSkill')
                ->findBy(array('advert' => $advert))
        ;

        return $this->render('OCPlatformBundle:Advert:view.html.twig',
                        array(
                    'advert' => $advert,
                    'listApplications' => $listApplications,
                    'listAdvertSkills' => $listAdvertSkills))
        ;
    }

    public function addAction(Request $request)
    {
        $advert = new Advert();
        $form = $this->createForm(new AdvertType(), $advert);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $event = new MessagePostEvent(
                    $advert->getContent(), $advert->getUser()
                    )
            ;

            $this
                    ->get('event_dispatcher')
                    ->dispatch(BigbrotherEvents::onMessagePost, $event)
            ;
            $advert->setContent($event->getMessage());
            $em = $this
                    ->getDoctrine()
                    ->getManager()
            ;
            $em->persist($advert);
            $em->flush();
            $request->getSession()->getFlashBag()->add('notice',
                    'Annonce bien enregistrée.');
            return $this->redirect($this->generateUrl(
                                    'oc_platform_view',
                                    array('id' => $advert->getId())));
        }
        return $this->render('OCPlatformBundle:Advert:add.html.twig',
                        array(
                    'form' => $form->createView()
        ));
    }

    public function editAction($id, Request $request)
    {
        $em = $this
                ->getDoctrine()
                ->getManager();

        $advert = $em
                ->getRepository('OCPlatformBundle:Advert')
                ->find($id);

        if (null === $advert)
        {
            throw new NotFoundHttpException("L'annonce d'id " . $id . " n'existe pas.");
        }

        $form = $this->createForm(new AdvertType(), $advert);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $em->flush();
            $request->getSession()->getFlashBag()->add('notice',
                    'Annonce bien modifiée..');
            return $this->redirect(
                            $this->generateUrl(
                                    'oc_platform_view',
                                    array(
                                'id' => $advert->getId()
                                    )
            ));
        }

        return $this->render('OCPlatformBundle:Advert:edit.html.twig',
                        array(
                    'advert' => $advert,
                    'form' => $form->createView()
        ));
    }

    public function deleteAction($id, Request $request)
    {
        $em = $this
                ->getDoctrine()
                ->getManager();

        $advert = $em
                ->getRepository('OCPlatformBundle:Advert')
                ->find($id);

        if (null === $advert)
        {
            throw new NotFoundHttpException("L'annonce d'id " . $id . " n'existe pas.");
        }

        $form = $this->createFormBuilder()->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $em->remove($advert);
            $em->flush();
            $request->getSession()->getFlashBag()->add('notice',
                    "L'annonce d'id " . $id . "  a bien été supprimée.");
            return $this->redirect(
                            $this->generateUrl('oc_platform_home')
                    )
            ;
        }
        return $this->render('OCPlatformBundle:Advert:delete.html.twig',
                        array(
                    'advert' => $advert,
                    'form' => $form->createView()
                        )
                )
        ;
    }

    public function menuAction($limit = 3)
    {
        $em = $this
                ->getDoctrine()
                ->getManager()
        ;

        $listAdverts = $em
                ->getRepository('OCPlatformBundle:Advert')
                ->findBy(array(), array(), $limit, 0)
        ;

        return $this->render('OCPlatformBundle:Advert:menu.html.twig',
                        array(
                    'listAdverts' => $listAdverts
        ));
    }

    public function editImageAction($advertId)
    {
        $em = $this->getDoctrine()->getManager();
        $advert = $em->getRepository('OCPlatformBundle:Advert')->find($advertId);
        $advert->getImage()->setUrl('test.png');

        $em->flush();

        return new Response('OK');
    }

    public function purgeAction($days)
    {
        $OCPurge = $this
                ->container
                ->get('oc_platform.advert_purger') // va chercher le service
        ;

        $listPurge = $OCPurge
                ->purge($days) // lance la purge des annonces vieilles de plus de $days jours
        ;
        return $this->render('OCPlatformBundle:Advert:purge.html.twig',
                        array('listPurge' => $listPurge));
    }

    public function translationAction($name)
    {
        return $this->render('OCPlatformBundle:Advert:translation.html.twig',
                        array(
                    'name' => $name
        ));
    }
}