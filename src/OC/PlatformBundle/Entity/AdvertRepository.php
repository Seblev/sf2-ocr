<?php

namespace OC\PlatformBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

class AdvertRepository extends EntityRepository
{
    /*
     * Renvoi la liste des annonces vieilles de plus de $days avec 0 candidatures
     */

    public function getAdvertListPurge($days) // paramètres X
    {
        $datePurge = new \DateTime(); // datetime actuel
        $datePurge->sub(new \DateInterval('P' . $days . 'D')); // soustrait $days jours à datetime - 

        $query = $this->createQueryBuilder('a')
                ->where('a.updatedAt < :datePurge OR a.updatedAt IS NULL') // date de mise à jour doit être plus récente que la date de purge OU prend le cas ou il n'y a jamais eu de màj
                ->andWhere('a.date < :datePurge') // date de création doit être plus récente que la date de purge
                ->setParameter('datePurge', $datePurge)
//              ->andWhere('a.nbApplications = 0') // vérifie que le compteur de candidatures est à 0, inutile puisque qu'on vérifie si il y en a pour de vrai en dessous
                ->andWhere('a.applications IS EMPTY') // vérifie qu'il n'y a aucune candidature
                ->getQuery()
        ;

        return $query
                        ->getResult()
        ;
    }

    public function getAdverts($page, $nbPerPage)
    {
        $query = $this->createQueryBuilder('a')
                ->leftJoin('a.image', 'i')
                ->addSelect('i')
                ->leftJoin('a.categories', 'c')
                ->addSelect('c')
                ->leftJoin('a.advertSkills', 'advs')
                ->addSelect('advs')
                ->leftJoin('advs.skill', 's')
                ->addSelect('s')
                ->orderBy('a.date', 'DESC')
                ->getQuery()
        ;
        $query
                ->setFirstResult(($page - 1) * $nbPerPage)
                ->setMaxResults($nbPerPage)
        ;

        return new Paginator($query, true);
    }

    public function getAdvertWithCategories(array $categoryNames)
    {
        $qb = $this
                ->createQueryBuilder('a')
        ;

        $qb
                ->join('a.categories', 'c')
                ->addSelect('c')
        ;

        /* Creates an IN() expression with the given arguments.
         *
         * @param string $x Field in string format to be restricted by IN() function.
         * @param mixed $y Argument to be used in IN() function.
         *
         * @return Expr\Func
         *
          public function in($x, $y)
          {
          if (is_array($y)) {
          foreach ($y as &$literal) {
          if ( ! ($literal instanceof Expr\Literal)) {
          $literal = $this->_quoteLiteral($literal);
          }
          }
          }
          return new Expr\Func($x . ' IN', (array) $y);
          }

         */
        $qb
                ->where($qb
                        ->expr()
                        ->in('c.name', $categoryNames))
        ;

        return $qb
                        ->getQuery()
                        ->getResult()
        ;
    }

    public function getPublishedQueryBuilder()
    {
        return $this
                        ->createQueryBuilder('a')
                        ->where('a.published = :published')
                        ->setParameter('published', true)
        ;
    }
}