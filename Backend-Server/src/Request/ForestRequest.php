<?php

namespace App\Request;

use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\String_;

class ForestRequest extends AbstractRequest
{
    protected function defineFilters(): array
    {
        return [
            ['key' => 'search', 'type' => String_::class]
        ];
    }

    protected function sortableField(): array
    {
        return ["f.id", "f.createdAt", "f.name"];
    }
}