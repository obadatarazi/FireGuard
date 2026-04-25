<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class DeleteEntityException extends HttpException
{

    public function __construct($message = "failed_delete_item")
    {
        parent::__construct(Response::HTTP_FAILED_DEPENDENCY, $message);
    }
}