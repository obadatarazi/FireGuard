<?php

namespace App\Request;

use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\String_;

class CenterRequest extends AbstractRequest
{
    protected function defineFilters(): array
    {
        return [
            ['key' => 'search', 'type' => String_::class],
            ['key' => 'phoneNumber', 'type' => String_::class],
            ['key' => 'status', 'type' => String_::class]
        ];
    }

    protected function sortableField(): array
    {
        return ["c.id", "c.createdAt", "c.name"];
    }
}