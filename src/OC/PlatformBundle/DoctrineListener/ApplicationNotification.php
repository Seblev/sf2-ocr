<?php

namespace OC\PlatformBundle\DoctrineListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use OC\PlatformBundle\Entity\Application;

class ApplicationNotification
{

    private $mailer;

    public function __construct(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!$entity instanceof Application)
        {
            return;
        }
        $message = new \Swift_Message(
                'Nouvelle candidature',
                'Vous avez reçu une nouvelle candidature.'
        );

        $message
                ->addTo($entity->getAdvert()->getAuthor().'@test.org')
                ->addFrom('admin@votresite.com')
        ;
        $this->mailer->send($message);
    }
}