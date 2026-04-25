<?php

namespace App\Service;

use App\Constant\RoleType;

class RolesService
{
    public function getAll(): array
    {
        $roles = RoleType::cases();

        return array_map(function (RoleType $roleType) {
            return $roleType->getRole();
        }, $roles);
    }

    public function getBySection(): array
    {
        $roles = RoleType::cases();
        $rolesArray = [];
        foreach ($roles as $role) {
            $section = $role->getSection();
            if (count($rolesArray) == 0
                || $rolesArray[count($rolesArray) - 1]['section'] != $section)
                $rolesArray[] = [
                    'section' => $section,
                    'roles' => []
                ];
            $rolesArray[count($rolesArray) - 1]['roles'][] = [
                'role' => $role->getRole(),
                'name' => $role->getName(),
                'description' => $role->getDesc(),
            ];
        }
        return $rolesArray;
    }


}