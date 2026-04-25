<?php

namespace App\ServiceException;

class EntityCannotBeUpdated extends \Exception
{

    public function __construct(string $message = 'Entity cannot be updated')
    {
        parent::__construct($message);
    }
}