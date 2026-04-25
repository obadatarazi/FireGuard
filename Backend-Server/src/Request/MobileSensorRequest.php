<?php

namespace App\Request;

use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\String_;

class MobileSensorRequest extends AbstractRequest
{
    protected function defineFilters(): array
    {
        return [
            ['key' => 'center', 'type' => Integer::class],
            ['key' => 'type', 'type' => String_::class],
            ['key' => 'status', 'type' => String_::class],
            ['key' => 'search', 'type' => String_::class],
        ];
    }

    protected function sortableField(): array
    {
        return ["ms.id", "ms.createdAt", "ms.name", "ms.type", "ms.status"];
    }
}

