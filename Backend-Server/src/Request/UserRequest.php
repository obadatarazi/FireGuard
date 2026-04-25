<?php

namespace App\Request;

use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\String_;

class UserRequest extends AbstractRequest
{

    protected function defineFilters(): array
    {
        return [
            ['key' => 'rolesGroup', 'type' => Integer::class],
            ['key' => 'search', 'type' => String_::class],
            ['key' => 'phoneNumber', 'type' => String_::class],
            ['key' => 'gender', 'type' => String_::class],
        ];
    }

    protected function sortableField(): array
    {
        return ["u.id", "u.fullName", "u.email", "u.createdAt"];
    }
}