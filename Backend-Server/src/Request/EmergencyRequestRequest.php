<?php

namespace App\Request;

use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\String_;


class EmergencyRequestRequest extends AbstractRequest
{
    protected function defineFilters(): array
    {
        return [
            ['key' => 'center', 'type' => Integer::class],
            ['key' => 'fire', 'type' => Integer::class],
            ['key' => 'fireBrigade', 'type' => Integer::class],
            ['key' => 'status', 'type' => String_::class],
        ];
    }

    protected function sortableField(): array
    {
        return ["em.id", "em.createdAt", "em.center"];
    }
}