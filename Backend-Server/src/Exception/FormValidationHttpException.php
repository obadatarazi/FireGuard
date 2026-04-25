<?php

namespace App\Exception;

use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class FormValidationHttpException extends HttpException
{

    private FormErrorIterator $formError;

    public function __construct(FormErrorIterator $formError)
    {
        parent::__construct(Response::HTTP_UNPROCESSABLE_ENTITY,"Validation Error");
        $this->formError = $formError;
    }

    public function getFormError(): FormErrorIterator
    {
        return $this->formError;
    }
}