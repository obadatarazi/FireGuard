<?php

namespace App\Service;

use App\Entity\Fire;
use App\Constant\FireStatusType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\ORM\Query;

class FireService extends AbstractService
{
    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        parent::__construct($em, $paginator, Fire::class);
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

    public function add(Fire $fire): void
    {
        parent::addEntity($fire);
    }

    public function update(Fire $fire): void
    {
        parent::updateEntity();
    }

    public function remove(Fire $fire): void
    {
        parent::delete($fire);
    }
    public function findAllPaginated($filters): Query
    {
        $forest = $filters['forest'];
        $status = $filters['status'];
        $device = $filters['device'];

        $qb = $this->repo
            ->createQueryBuilder('fi')
            ->where('1 = 1');

        if ($forest) {
            $qb->andWhere('fi.forest = :forest')->setParameter('forest', $forest);
        }

        if ($status) {
            $qb->andWhere('fi.status = :status')->setParameter('status', $status);
        }

        if ($device) {
            $qb->andWhere('fi.device = :device')->setParameter('device', $device);
        }

        return $qb->getQuery();
    }
    
    public function cronJobCreateFire($deviceValue) 
    {
        $fire = new Fire();
        $fire->setForest($deviceValue->getDevice()->getForest());
        $fire->setDevice($deviceValue->getDevice());
        $fire->setStatus(FireStatusType::OnFire->value);
            
        $this->add($fire);
    }
    
    public function findAll() 
    {
        $qb = $this->repo->createQueryBuilder('fi');
        return $qb->getQuery()->getResult();
    }
}