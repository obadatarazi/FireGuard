<?php

namespace App\Service;

use App\Entity\Car;
use App\Lib\SearchHelper;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\ORM\Query;

class CarService extends AbstractService
{
    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        parent::__construct($em, $paginator, Car::class);
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

    public function add(Car $car): void
    {
        parent::addEntity($car);
    }

    public function update(Car $car): void
    {
        parent::updateEntity();
    }

    public function remove(Car $car): void
    {
        parent::delete($car);
    }

    public function findAllPaginated($filters): Query
    {
        $search = $filters['search'];
        $numberPlate = $filters['numberPlate'];
        $model = $filters['model'];
        $center = $filters['center'];

        $qb = $this->repo
            ->createQueryBuilder('ca')
            ->where('1 = 1');

        if ($search) {
            $qb->andWhere('MATCH (ca.name) AGAINST (:search IN BOOLEAN MODE) > 0');
            $search = SearchHelper::convertToAutoCompleteTerm($search);
            $qb->setParameter('search', $search);
        }

        if ($numberPlate) {
            $qb->andWhere('ca.numberPlate = :numberPlate')
                ->setParameter('numberPlate', $numberPlate);
        }

        if ($model) {
            $qb->andWhere('ca.model = :model')
                ->setParameter('model', $model);
        }

        if ($center) {
            $qb->andWhere('ca.center = :center')
                ->setParameter('center', $center);
        }

        return $qb->getQuery();
    }
}