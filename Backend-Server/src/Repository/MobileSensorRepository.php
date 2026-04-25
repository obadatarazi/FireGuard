<?php

namespace App\Repository;

use App\Entity\MobileSensor;
use Doctrine\Persistence\ManagerRegistry;

class MobileSensorRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MobileSensor::class);
    }
}

