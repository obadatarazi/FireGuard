<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

abstract class AbstractRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
    }

    public function persist($entity)
    {
        $this->_em->persist($entity);
    }

    public function save($entity)
    {
        $this->_em->persist($entity);
        $this->_em->flush();
    }

    public function remove($entity)
    {
        $this->_em->remove($entity);
//        $this->_em->flush();
    }

    public function update($entity = null)
    {
        if ($entity == null)
            $this->_em->flush();
        $this->_em->flush($entity);
    }

    public function findAllPaginated($filters)
    {
        // TODO: Implement findAllPaginated() method.
    }

    public function changeSet($entity): array
    {
        if ($entity == null)
            return array();
        $uow = $this->_em->getUnitOfWork();
        $uow->computeChangeSets();
        return $uow->getEntityChangeSet($entity);
    }
}