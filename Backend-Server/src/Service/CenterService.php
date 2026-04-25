<?php

namespace App\Service;

use App\Entity\Center;
use App\Lib\SearchHelper;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\ORM\Query;

class CenterService extends AbstractService
{
    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        parent::__construct($em, $paginator, Center::class);
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

    public function add(Center $center): void
    {
        parent::addEntity($center);
    }

    public function update(Center $center): void
    {
        parent::updateEntity();
    }

    public function remove(Center $center): void
    {
        parent::delete($center);
    }

    public function findAllPaginated($filters): Query
    {
        $search = $filters['search'];
        $phoneNumber = $filters['phoneNumber'];
        $status = $filters['status'];

        $qb = $this->repo
            ->createQueryBuilder('c')
            ->where('1 = 1');

        if ($search) {
            $qb->andWhere('MATCH (c.name) AGAINST (:search IN BOOLEAN MODE) > 0');
            $search = SearchHelper::convertToAutoCompleteTerm($search);
            $qb->setParameter('search', $search);
        }

        if ($phoneNumber) {
            $qb->andWhere('c.phoneNumber = :phoneNumber')
                ->setParameter('phoneNumber', $phoneNumber);
        }

        if ($status) {
            $qb->andWhere('c.status = :status')
                ->setParameter('status', $status);
        }

        return $qb->getQuery();
    }
    
    public function findAll() 
    {
        $qb = $this->repo->createQueryBuilder('c');
        return $qb->getQuery()->getResult();
    }
}