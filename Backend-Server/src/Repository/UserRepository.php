<?php

namespace App\Repository;

use App\Constant\RolesGroupsIdentifiers;
use App\Entity\User;
use App\Lib\SearchHelper;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserRepository extends AbstractRepository implements UserLoaderInterface, PasswordUpgraderInterface
{

    public function __construct(ManagerRegistry    $registry)
    {
        parent::__construct($registry, User::class);
    }


    public function findAllPaginated($filters)
    {
        $search = $filters['search'];
        $phoneNumber = $filters['phoneNumber'];
        $rolesGroup = $filters['rolesGroup'];
        $gender = $filters['gender'];

        $qb = $this
            ->createQueryBuilder('u')
            ->leftJoin('u.rolesGroups', 'rg')
            ->where('1 = 1');

        if ($search) {
            $qb->andWhere(' MATCH (u.email,u.fullName,u.phoneNumber) AGAINST (:search IN BOOLEAN MODE) > 0');
            $search = SearchHelper::convertToAutoCompleteTerm($search);
            $qb->setParameter('search', $search);
        }

        if ($phoneNumber) {
            $qb->andWhere('u.phoneNumber like :phoneNumber');
            $qb->setParameter('phoneNumber', '%' . $phoneNumber . '%');
        }

        if ($gender) {
            $qb->andWhere('u.gender LIKE :gender');
            $qb->setParameter('gender', '%' . $gender . '%');
        }

        if ($rolesGroup) {
            $qb->andWhere('rg.id = :rolesGroup');
            $qb->setParameter('rolesGroup', $rolesGroup);
        }

        return $qb->getQuery();
    }

    public function findUserByRoles(array $roles)
    {
        $roles_expression = array_reduce($roles, function ($carry, $item) {
            $carry .= "+{$item} ";
            return $carry;
        });

        $qb = $this
            ->createQueryBuilder('u')
            ->innerJoin('u.rolesGroups', 'rg')
            ->where('MATCH (rg.roles) AGAINST (:roles IN BOOLEAN MODE) > 0')
            ->setParameter('roles', $roles_expression);

        $query = $qb->getQuery();

        return $query->getResult();
    }

    public function loadUserByEmail(string $email): ?UserInterface
    {
        $entityManager = $this->getEntityManager();
        $qb = $entityManager->createQueryBuilder();
        $qb
            ->select('u')
            ->from(User::class, 'u')
            ->where('u.email = :email')
            ->andWhere('u.active = true');
        $query = $qb->getQuery();
        $query->setParameter('email', $email);
        return $query->getOneOrNullResult();
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     * @param PasswordAuthenticatedUserInterface $user
     * @param string $newHashedPassword
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    public function loadUserByIdentifier(string $identifier): ?UserInterface
    {
        return $this->loadUserByEmail($identifier);
    }

    public function refresh(User $user)
    {
        $this->_em->refresh($user);
    }

    public function getMonthlyRegisteredUsers()
    {
        $date = new \DateTime();
        $date = $date->format('Y-m-d');
        $dateBeforeMonth = new \DateTime('-1 month');
        $dateBeforeMonth = $dateBeforeMonth->format('Y-m-d');
        $qb = $this->createQueryBuilder('u')
        ->select('DATE(u.createdAt) as createdAt , COUNT(u.id) as count ')
        ->where('DATE(u.createdAt) BETWEEN :dateBeforeMonth AND :date')
        ->groupBy('createdAt')
        ->orderBy('createdAt','desc')
        ->setParameter('date', $date)
        ->setParameter('dateBeforeMonth' , $dateBeforeMonth);

        return $qb->getQuery()->getResult();
    }

    public function getUserCount()
    {
        $qb = $this->createQueryBuilder('u')
        ->select('COUNT(u.id) as count');

        $query = $qb->getQuery()->getSingleResult();

        return $query;
    }
}