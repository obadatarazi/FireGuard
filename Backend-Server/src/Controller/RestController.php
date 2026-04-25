<?php

namespace App\Controller;

use App\Exception\FormValidationHttpException;
use App\Service\RestHelperService;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

abstract class RestController extends AbstractFOSRestController
{

    protected EntityManagerInterface $em;
    protected RestHelperService $rest;
    protected array $groups;

    public function __construct(RestHelperService $rest, EntityManagerInterface $em)
    {
        $this->rest = $rest;
        $this->em = $em;
        $this->groups = ['Default'];
    }

    public function setGroup(array $groups): self
    {
        $this->groups = $groups;

        return $this;
    }

    public function clearGroup(): self
    {
        $this->groups = ['Default'];

        return $this;
    }

    /**
     * @throws FormValidationHttpException
     */
    protected function submitAttempt(FormInterface $form, $requestBody, bool $clearMissing = true): void
    {
        $form->submit($requestBody, $clearMissing);

        $isValidRequest = $form->isSubmitted() && $form->isValid();

        // if (!$isValidRequest)
        //     throw new FormValidationHttpException($form->getErrors());
    }

    protected function viewResponse($data = null, ?int $statusCode = null, array $headers = []): Response
    {
        $view = $this->view($data, $statusCode, $headers);
        $view->getContext()->setGroups($this->groups);
        return $this->handleView($view);
    }

}