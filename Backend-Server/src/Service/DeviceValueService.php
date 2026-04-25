<?php

namespace App\Service;

use App\Entity\DeviceValue;
use App\Service\FireService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\ORM\Query;

class DeviceValueService extends AbstractService
{
    private FireService $fireService;
    
    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator, FireService $fireService)
    {
        parent::__construct($em, $paginator, DeviceValue::class);
        $this->fireService = $fireService;
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

    public function add(DeviceValue $deviceValue): void
    {
        parent::addEntity($deviceValue);
        
        if ($deviceValue->getStatus() === 'Dangerous' || $deviceValue->getStatus() === 'dangerous') 
        {
            $this->fireService->cronJobCreateFire($deviceValue);
        }
        
    }

    public function update(DeviceValue $deviceValue): void
    {
        parent::updateEntity();
    }

    public function remove(DeviceValue $deviceValue): void
    {
        parent::delete($deviceValue);
    }
    public function findAllPaginated($filters): Query
    {
        $device = $filters['device'];

        $qb = $this->repo
            ->createQueryBuilder('sv')
            ->where('1 = 1');

        if ($device) {
            $qb->andWhere('sv.device = :device')
                ->setParameter('device', $device);
        }

        return $qb->getQuery();
    }
    
    public function createDeviceValueByDevice($device)
    {
        $deviceValue = new DeviceValue();
        
        $deviceValue->setDevice($device);
        $deviceValue->setValueHeat(0.00);
        $deviceValue->setValueMoisture(0.00);
        $deviceValue->setValueGas(0.00);
        $deviceValue->setStatus("INITIAL");
        $deviceValue->setDate(new \DateTime());
        
        $this->add($deviceValue);
    }
    
}