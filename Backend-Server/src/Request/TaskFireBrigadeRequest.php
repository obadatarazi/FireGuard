<?php

namespace App\Request;

use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\String_;

class TaskFireBrigadeRequest extends AbstractRequest
{
    protected function defineFilters(): array
    {
        return [
            ['key' => 'fire', 'type' => Integer::class],
            ['key' => 'fireBrigade', 'type' => Integer::class],
            ['key' => 'status', 'type' => String_::class],

        ];
    }

    protected function sortableField(): array
    {
        return ["tf.id", "tf.createdAt", "tf.fire"];
    }
}