<?php

namespace OC\PlatformBundle\Purger;

class OCPurger
{

    private $days; // nombre de jours pour la purge
    private $em; // entity-manager

    public function __construct(\Doctrine\ORM\EntityManager $em) // injection de l'entity-manager
    {
        $this
                ->em = $em
        ;
    }

    /**
     * Purge advert without applications oldest than $days
     */
    public function purge($days)
    {
        $listPurge = $this
                ->em
                ->getRepository('OCPlatformBundle:Advert')
                ->getAdvertListPurge($days)
        ;

        foreach ($listPurge AS $purge) // pour chaque annonce qui correspond à la purge
        {
            $this
                    ->em
                    ->remove($purge) // donne l'ordre d'effacer l'annonce
            ;
        }

        $this
                ->em
                ->flush() // lance la suppression effective
        ;

        return $listPurge; // renvoi la liste des éléments purgés afin de l'afficher
    }
}