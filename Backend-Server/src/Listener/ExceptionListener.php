<?php

namespace App\Listener;

use App\Exception\DeleteEntityException;
use App\Exception\FormValidationHttpException;
use App\Service\RestHelperService;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Translation\LocaleSwitcher;
use Symfony\Contracts\Translation\TranslatorInterface;
use UnexpectedValueException;

class ExceptionListener
{

    private RestHelperService $rest;
    private TranslatorInterface $translator;
    private LocaleSwitcher $switcher;
    private ParameterBagInterface $parameterBag;

    public function __construct(
        RestHelperService $rest,
        ParameterBagInterface $parameterBag,
        TranslatorInterface $translator,
        LocaleSwitcher $switcher
    ) {
        $this->rest = $rest;
        $this->translator = $translator;
        $this->switcher = $switcher;
        $this->parameterBag = $parameterBag;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        // You get the exception object from the received event
        $exception = $event->getThrowable();
        $response = new JsonResponse();
        $requestUri = $event->getRequest()->getRequestUri();
        $isApiPrefix = str_starts_with($requestUri, "/backend/public/api");
        if ($isApiPrefix) {
            if ($exception instanceof NotFoundHttpException) {

                $responseArray = $this->rest
                    ->failed()
                    ->setCustom('error', $this->translator->trans('not_found'))
                    ->getResponse();
                $response->setData($responseArray);

                $event->setResponse($response);
            } else if ($exception instanceof UnexpectedValueException) {

                $responseArray = $this->rest
                    ->failed()
                    ->setCustom('error', $this->translator->trans('invalid_value'))
                    ->getResponse();

                $response->setData($responseArray);

                $event->setResponse($response);
            } else if ($exception instanceof FormValidationHttpException) {

                $responseArray = $this->rest
                    ->failed()
                    ->setFormErrors($exception->getFormError())
                    ->getResponse();

                $response->setData($responseArray);

                $event->setResponse($response);
            } else if ($exception instanceof ForeignKeyConstraintViolationException) {

                $responseArray = $this->rest->failed()
                    ->setCustom('error', $this->translator->trans('restrict_deletion'))
                    ->getResponse();

                $response = new JsonResponse($responseArray);

                $event->setResponse($response);
            } else if ($exception instanceof BadRequestHttpException) {

                $responseArray = $this->rest->failed()
                    ->setCustom('error', $this->translator->trans($exception->getMessage()))
                    ->getResponse();

                $response = new JsonResponse($responseArray);

                $event->setResponse($response);
            } else if ($exception instanceof DeleteEntityException) {

                $responseArray = $this->rest->failed()
                    ->setCustom('error', $this->translator->trans($exception->getMessage()))
                    ->getResponse();

                $response = new JsonResponse($responseArray);
                $event->setResponse($response);
            } else if ($exception instanceof AccessDeniedHttpException) {

                $responseArray = $this->rest->failed()
                    ->setCustom('error', $this->translator->trans('access_denied'))
                    ->getResponse();

                $response = new JsonResponse($responseArray);

                $event->setResponse($response);
            } else if ($exception instanceof OptimisticLockException) {

                $responseArray = $this->rest->failed()
                    ->setCustom('error', $this->translator->trans('optimistic_lock_code_error'))
                    ->getResponse();

                $response = new JsonResponse($responseArray);

                $event->setResponse($response);
            } else if ($exception instanceof UniqueConstraintViolationException) {

                $responseArray = $this->rest->failed()
                    ->setCustom('error', $this->translator->trans('duplicate_entry'))
                    ->getResponse();

                $response = new JsonResponse($responseArray);
                $event->setResponse($response);
            } else {

                $responseArray = $this->rest->failed()
                    ->setCustom('error', $this->translator->trans($exception->getMessage()))
                    ->getResponse();

                $response = new JsonResponse($responseArray);
                $event->setResponse($response);
            }
        }
    }
}
