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
        /*
         * Récupère la liste des adverts à purger
         */
        $listPurge = $this
                ->em
                ->getRepository('OCPlatformBundle:Advert')
                ->getAdvertListPurge($days)
        ;

        /*
         * Pour chaque annonce qui correspond à la purge
         */
        foreach ($listPurge AS $purge)
        {
            /* Donne l'ordre d'effacer l'annonce 
             * et se qui lui est associé (image, advertSkill, advert_category) 
             * si ils ont une cascade de remove
             */
            $this
                    ->em
                    ->remove($purge)
            ;
        }
        /*
         * Lance la suppression effective
         */
        $this
                ->em
                ->flush()
        ;
/*
 * renvoi la liste des éléments purgés afin de l'afficher
 */
        return $listPurge;
    }
}