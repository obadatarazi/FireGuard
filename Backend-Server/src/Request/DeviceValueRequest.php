<?php

namespace App\Request;

use phpDocumentor\Reflection\Types\Integer;

class DeviceValueRequest extends AbstractRequest
{
    protected function defineFilters(): array
    {
        return [
            ['key' => 'device', 'type' => Integer::class]
        ];
    }

    protected function sortableField(): array
    {
        return ["sv.id", "sv.createdAt", "sv.device"];
    }
}