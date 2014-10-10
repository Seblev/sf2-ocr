<?php

namespace OC\PlatformBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

class AdvertRepository extends EntityRepository
{

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
}