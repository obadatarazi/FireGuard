<?php

namespace App\ServiceException;

class EntityCannotBeDeleted extends \Exception
{

    public function __construct(string $message = 'Entity cannot be deleted')
    {
        parent::__construct($message);
    }
}