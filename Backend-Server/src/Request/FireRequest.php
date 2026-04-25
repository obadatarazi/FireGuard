<?php

namespace App\Request;

use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\String_;

class FireRequest extends AbstractRequest
{
    protected function defineFilters(): array
    {
        return [
            ['key' => 'forest', 'type' => Integer::class],
            ['key' => 'status', 'type' => String_::class],
            ['key' => 'device', 'type' => Integer::class]
        ];
    }

    protected function sortableField(): array
    {
        return ["fi.id", "fi.createdAt", "fi.forest"];
    }
}