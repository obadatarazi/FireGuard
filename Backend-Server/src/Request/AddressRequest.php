<?php

namespace App\Request;

use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\String_;

class AddressRequest extends AbstractRequest
{
    protected function defineFilters(): array
    {
        return [];
    }

    protected function sortableField(): array
    {
        return ["a.id", "a.createdAt"];
    }
}