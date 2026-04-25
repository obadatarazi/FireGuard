<?php

namespace App\Request;

use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\String_;

class CarRequest extends AbstractRequest
{
    protected function defineFilters(): array
    {
        return [
            ['key' => 'search', 'type' => String_::class],
            ['key' => 'numberPlate', 'type' => String_::class],
            ['key' => 'model', 'type' => String_::class],
            ['key' => 'center', 'type' => Integer::class]
        ];
    }

    protected function sortableField(): array
    {
        return ["ca.id", "ca.createdAt", "ca.name"];
    }
}