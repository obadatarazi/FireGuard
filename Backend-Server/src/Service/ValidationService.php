<?php

namespace App\Service;

use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Integer;

class ValidationService
{
    public function parseBoolOrPut($value): bool
    {
        if (is_numeric($value))
            return $value == 1;
        return $value == "true";
    }

    public function parseIntOrPut($value, $default): int
    {
        if (!is_numeric($value)) {
            $value = $default;
        }
        return $value;
    }

    public function inArrayOrPut($value, array $array, $default = "")
    {
        if (!in_array($value, $array))
            $value = $default;

        return $value;
    }

    public function validate($value, $type)
    {
        if ($type == Integer::class)
            return $this->parseIntOrPut($value, 0);
        else if ($type == Boolean::class)
            return $this->parseBoolOrPut($value);
        return $value;
    }
}