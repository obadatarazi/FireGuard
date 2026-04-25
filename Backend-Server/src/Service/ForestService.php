<?php

namespace App\Service;

use App\Entity\Forest;
use App\Lib\SearchHelper;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\ORM\Query;

class ForestService extends AbstractService
{
    
    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        parent::__construct($em, $paginator, Forest::class);
    }

    public function getList($page, $limit, $filters): PaginationInterface
    {
        $query = $this->findAllPaginated($filters);
        return $this->paginator->paginate(
            $query,
            $page,
            $limit
        );
    }

    public function add(Forest $forest): void
    {
        parent::addEntity($forest);
    }

    public function update(Forest $forest): void
    {
        parent::updateEntity();
    }

    public function remove(Forest $forest): void
    {
        parent::delete($forest);
    }

    public function findAllPaginated($filters): Query
    {
        $search = $filters['search'];

        $qb = $this->repo
            ->createQueryBuilder('f')
            ->where('1 = 1');

        if ($search) {
            $qb->andWhere(' MATCH (f.name) AGAINST (:search IN BOOLEAN MODE) > 0');
            $search = SearchHelper::convertToAutoCompleteTerm($search);
            $qb->setParameter('search', $search);
        }

        return $qb->getQuery();
    }
    
    public function findAll() 
    {
        $qb = $this->repo->createQueryBuilder('f');
        return $qb->getQuery()->getResult();
    }
}