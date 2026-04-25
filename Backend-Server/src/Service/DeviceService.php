<?php

namespace App\Service;

use App\Entity\Device;
use App\Entity\DeviceValue;
use App\Lib\SearchHelper;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\ORM\Query;
use App\Service\DeviceValueService;

class DeviceService extends AbstractService
{
    private DeviceValueService $deviceValueService;
    
    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator, DeviceValueService $deviceValueService)
    {
        parent::__construct($em, $paginator, Device::class);
        $this->deviceValueService = $deviceValueService;
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

    public function add(Device $device): void
    {
        parent::addEntity($device);
        
        $this->deviceValueService->createDeviceValueByDevice($device);
    }

    public function update(Device $device): void
    {
        parent::updateEntity();
    }

    public function remove(Device $device): void
    {
        parent::delete($device);
    }
    public function findAllPaginated($filters): Query
    {
        $search = $filters['search'];
        $forest = $filters['forest'];

        $qb = $this->repo
            ->createQueryBuilder('s')
            ->where('1 = 1');

        if ($search) {
            $qb->andWhere(' MATCH (s.name) AGAINST (:search IN BOOLEAN MODE) > 0');
            $search = SearchHelper::convertToAutoCompleteTerm($search);
            $qb->setParameter('search', $search);
        }

        if ($forest) {
            $qb->andWhere('s.forest = :forest')
                ->setParameter('forest', $forest);
        }

        return $qb->getQuery();
    }
   
    public function findAll() 
    {
        $qb = $this->repo->createQueryBuilder('s');
        return $qb->getQuery()->getResult();
    }
}