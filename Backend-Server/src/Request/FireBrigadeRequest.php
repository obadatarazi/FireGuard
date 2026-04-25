<?php

namespace App\Request;

use phpDocumentor\Reflection\Types\Integer;

class FireBrigadeRequest extends AbstractRequest
{
    protected function defineFilters(): array
    {
        return [
            ['key' => 'center', 'type' => Integer::class]
        ];
    }

    protected function sortableField(): array
    {
        return ["fb.id", "fb.createdAt", "fb.name"];
    }
}