<?php

namespace App\Service;

use App\Entity\MobileSensor;
use App\Lib\SearchHelper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class MobileSensorService extends AbstractService
{
    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        parent::__construct($em, $paginator, MobileSensor::class);
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

    public function add(MobileSensor $mobileSensor): void
    {
        parent::addEntity($mobileSensor);
    }

    public function update(MobileSensor $mobileSensor): void
    {
        parent::updateEntity();
    }

    public function remove(MobileSensor $mobileSensor): void
    {
        parent::delete($mobileSensor);
    }

    public function findAllPaginated($filters): Query
    {
        $center = $filters['center'];
        $type = $filters['type'];
        $status = $filters['status'];
        $search = $filters['search'];

        $qb = $this->repo
            ->createQueryBuilder('ms')
            ->where('1 = 1');

        if ($center) {
            $qb->andWhere('ms.center = :center')
                ->setParameter('center', $center);
        }

        if ($type) {
            $qb->andWhere('ms.type = :type')
                ->setParameter('type', $type);
        }

        if ($status) {
            $qb->andWhere('ms.status = :status')
                ->setParameter('status', $status);
        }

        if ($search) {
            $qb->andWhere('MATCH (ms.name) AGAINST (:search IN BOOLEAN MODE) > 0');
            $search = SearchHelper::convertToAutoCompleteTerm($search);
            $qb->setParameter('search', $search);
        }

        return $qb->getQuery();
    }
}

