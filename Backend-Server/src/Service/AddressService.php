<?php

namespace App\Service;

use App\Entity\Address;
use App\Lib\SearchHelper;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\ORM\Query;

class AddressService extends AbstractService
{
    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        parent::__construct($em, $paginator, Address::class);
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

    public function add(Address $address): void
    {
        parent::addEntity($address);
    }

    public function update(Address $address): void
    {
        parent::updateEntity();
    }

    public function remove(Address $address): void
    {
        parent::delete($address);
    }
    public function findAllPaginated($filters): Query
    {
        $qb = $this->repo
            ->createQueryBuilder('a')
            ->where('1 = 1');

        return $qb->getQuery();
    }
}