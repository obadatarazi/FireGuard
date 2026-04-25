<?php

namespace App\Request;

use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\String_;

class RolesGroupRequest extends AbstractRequest
{
    protected function defineFilters(): array
    {
        return [
            ['key' => 'standard', 'type' => Boolean::class],
            ['key' => 'search', 'type' => String_::class],
        ];
    }

    protected function sortableField(): array
    {
        return ["rg.id", "rg.name", "rg.createdAt"];
    }
}