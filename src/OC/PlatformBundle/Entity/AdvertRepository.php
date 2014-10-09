<?php

namespace OC\PlatformBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class AdvertRepository extends EntityRepository
{

    public function myFind()
    {
        $qb = $this->createQueryBuilder('a');
        // On peut ajouter ce qu'on veut avant
        $qb
                ->where('a.author = :author')
                ->setParameter('author', 'Marine')
        ;
        // On applique notre condition sur le QueryBuilder
        $this->whereCurrentYear($qb);
        // On peut ajouter ce qu'on veut après
        $qb->orderBy('a.date', 'DESC');
        return $qb
                        ->getQuery()
                        ->getResult()
        ;
    }

    public function whereCurrentYear(QueryBuilder $qb)
    {
        $qb
                ->andWhere('a.date BETWEEN :start AND :end')
                ->setParameter('start', new \Datetime(date('Y') . '-01-01'))  // Date entre le 1er janvier de cette année
                ->setParameter('end', new \Datetime(date('Y') . '-12-31'))  // Et le 31 décembre de cette année
        ;

        return $qb;
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
}