<?php

namespace App\Service;

use App\Constant\EmergencyRequestStatus;
use App\Entity\EmergencyRequest;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\ORM\Query;
use App\Entity\DeviceValue;


class EmergencyRequestService extends AbstractService
{
    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        parent::__construct($em, $paginator, EmergencyRequest::class);
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

    public function add(EmergencyRequest $emergencyRequest): void
    {
        $emergencyRequest->setStatus(EmergencyRequestStatus::Dangerous->value);
        parent::addEntity($emergencyRequest);
    }

    public function update(EmergencyRequest $emergencyRequest): void
    {
        parent::updateEntity();
    }

    
    public function findAllPaginated($filters): Query
    {
        $center = $filters['center'];
        $fire = $filters['fire'];
        $fireBrigade = $filters['fireBrigade'];
        $status = $filters['status'];

        $qb = $this->repo
            ->createQueryBuilder('em')
            ->where('1 = 1');


        if ($center) {
            $qb->andWhere('em.center = :center')
                ->setParameter('center', $center);
        }

        if ($fire) {
            $qb->andWhere('em.fire = :fire')
                ->setParameter('fire', $fire);
        }
        
        if ($fireBrigade) {
            $qb->andWhere('em.fireBrigade = :fireBrigade')
                ->setParameter('fireBrigade', $fireBrigade);
        }
        
        if ($status) {
            $qb->andWhere('em.status = :status')
                ->setParameter('status', $status);
        }

        return $qb->getQuery();
    }
}