<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Knp\Component\Pager\PaginatorInterface;

abstract class AbstractService
{
    protected EntityManagerInterface $em;
    protected EntityRepository $repo;
    protected PaginatorInterface $paginator;

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator, string $entityClass)
    {
        $this->em = $em;
        $this->paginator = $paginator;
        $this->repo = $this->em->getRepository($entityClass);
    }

    public function getById($id)
    {
        return $this->repo->find($id);
    }

    public function addEntity($entity): void
    {
        $this->em->persist($entity);
        $this->em->flush();
    }

    public function updateEntity(): void
    {
        $this->em->flush();
    }

    public function delete($entity): void
    {
        $this->em->remove($entity);
        $this->em->flush();
    }

    public function refresh($entity)
    {
        $this->em->getUnitOfWork()->refresh($entity);

    }

}
