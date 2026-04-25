<?php

namespace App\Service;

use App\Entity\FireBrigade;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\ORM\Query;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class FireBrigadeService extends AbstractService
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator, UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
        parent::__construct($em, $paginator, FireBrigade::class);
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

    public function add(FireBrigade $fireBrigade, $password): void
    {
        if ($password) {
            $hashedPassword = $this->passwordHasher->hashPassword($fireBrigade, $password);
            $fireBrigade->setPassword($hashedPassword);
        }
        parent::addEntity($fireBrigade);
    }

    public function update(FireBrigade $fireBrigade): void
    {
        parent::updateEntity();
    }

    public function remove(FireBrigade $fireBrigade): void
    {
        parent::delete($fireBrigade);
    }
    public function findAllPaginated($filters): Query
    {
        $center = $filters['center'];

        $qb = $this->repo
            ->createQueryBuilder('fb')
            ->where('1 = 1');

        if ($center) {
            $qb->andWhere('fb.center = :center')->setParameter('center', $center);
        }

        return $qb->getQuery();
    }

    public function changeUserPassword(FireBrigade $fireBrigade, $newPassword): bool
    {
        $hashedNewPassword = $this->passwordHasher->hashPassword($fireBrigade, $newPassword);
        $fireBrigade->setPassword($hashedNewPassword);
        $this->update($fireBrigade);
        return true;
    }

    public function isMatchedPassword(string $newPassword, string $confirmPassword): bool
    {
        return $newPassword == $confirmPassword;
    }
}