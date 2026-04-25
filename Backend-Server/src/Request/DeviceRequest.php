<?php

namespace App\Request;

use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\String_;

class DeviceRequest extends AbstractRequest
{
    protected function defineFilters(): array
    {
        return [
            ['key' => 'search', 'type' => String_::class],
            ['key' => 'forest', 'type' => Integer::class]
        ];
    }

    protected function sortableField(): array
    {
        return ["s.id", "s.createdAt", "s.name"];
    }
}