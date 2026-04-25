<?php

namespace App\Service;

use App\Entity\TaskFireBrigade;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\ORM\Query;

class TaskFireBrigadeService extends AbstractService
{
    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        parent::__construct($em, $paginator, TaskFireBrigade::class);
    }
    
    public function getFireByTaskFireBridage($fire) 
    {
        $qb = $this->repo->createQueryBuilder('tf')->where('tf.fire = :fire')
            ->setParameter('fire', $fire);
            
        return $qb->getQuery()->getResult();
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

    public function add(TaskFireBrigade $taskFireBrigade): void
    {
        $taskFireBrigade->setStatus($taskFireBrigade->getFire()->getStatus());
        parent::addEntity($taskFireBrigade);
    }

    public function update(TaskFireBrigade $taskFireBrigade): void
    {
        $statusTask = $this->getFireByTaskFireBridage($taskFireBrigade->getFire());
        $status = $statusTask[0]->getStatus();
        $statusTask[0]->getFire()->setStatus($status);
        
        parent::updateEntity();
    }

    public function remove(TaskFireBrigade $taskFireBrigade): void
    {
        parent::delete($taskFireBrigade);
    }
    public function findAllPaginated($filters): Query
    {
        $fire = $filters['fire'];
        $fireBrigade = $filters['fireBrigade'];
        $status = $filters['status'];
        $qb = $this->repo
            ->createQueryBuilder('tf')
            ->where('1 = 1');

        if ($fire) {
            $qb->andWhere('tf.fire = :fire')->setParameter('fire', $fire);
        }

        if ($fireBrigade) {
            $qb->andWhere('tf.fireBrigade = :fireBrigade')->setParameter('fireBrigade', $fireBrigade);
        }
        if ($status){
            $qb->andWhere('tf.status = :status')->setParameter('status', $status);
        }

        return $qb->getQuery();
    }
}