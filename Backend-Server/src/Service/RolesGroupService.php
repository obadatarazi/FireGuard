<?php

namespace App\Service;

use App\Constant\RolesGroupsIdentifiers;
use App\Entity\RolesGroup;
use App\Lib\SearchHelper;
use App\ServiceException\EntityCannotBeDeleted;
use App\ServiceException\EntityCannotBeUpdated;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;


class RolesGroupService extends AbstractService
{

    public function __construct(EntityManagerInterface $em ,PaginatorInterface $paginator)
    {
        parent::__construct($em, $paginator, RolesGroup::class);
    }

    public function getList($page, $limit, $filters): PaginationInterface
    {
        $query = $this->findAllPaginated($filters);
        return $this->paginator->paginate(
            $query,
            $page,
            $limit,
            ['wrap-queries' => true]
        );
    }

    public function add(RolesGroup $rolesGroup): void
    {
        parent::addEntity($rolesGroup);
    }

    public function update(RolesGroup $rolesGroup): void
    {
        if ($rolesGroup->isStandard()) {
            throw new EntityCannotBeUpdated('update_standard_roles_group');
        }
        parent::updateEntity();
    }

    public function delete($rolesGroup): void
    {
        if ($rolesGroup->isStandard()) {
            throw new EntityCannotBeDeleted('delete_standard_roles_group');
        }
        parent::delete($rolesGroup);
    }

    public function getDefaultRoleGroup()
    {
        return $this->repo->findOneByIdentifier(RolesGroupsIdentifiers::User->value);
    }

    public function getSuperAdminRoleGroup(): RolesGroup
    {
        return $this->repo->findOneByIdentifier(RolesGroupsIdentifiers::SuperAdmin->value);
    }

    public function updateStandard(RolesGroup $rolesGroup): void
    {
        parent::updateEntity();
    }

    private function findAllPaginated($filters)
    {
        $standard = $filters['standard'];
        $search = $filters['search'];

        $qb = $this->repo
            ->createQueryBuilder('rg')
            ->where('1=1');

        if ($search) {
            $qb->andWhere(' MATCH (rg.name,rg.roles) AGAINST (:search IN BOOLEAN MODE) > 0');
            $search = SearchHelper::convertToAutoCompleteTerm($search);
            $qb->setParameter('search', $search);
        }

        if ($standard !== null) {
            $qb->andWhere('rg.standard = (:standard)');
            $qb->setParameter('standard', $standard);
        }

        return $qb->getQuery();
    }

}
