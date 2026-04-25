<?php

namespace App\Service;

use Exception;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\Form\FormInterface;

class RestHelperService
{

    protected bool $success = true;
    protected $formErrors = null;
    protected array $messages = [];
    protected $data = null;
    protected $pagination = null;
    protected array $custom = [];

    public function reset(): self
    {
        $this->success = true;
        $this->messages = [];
        $this->custom = [];
        return $this;
    }

    public function succeeded(): self
    {
        $this->success = true;
        return $this;
    }

    public function failed(): self
    {
        $this->success = false;
        return $this;
    }

    public function isSucceeded(): bool
    {
        return $this->success;
    }

    public function isFailed(): bool
    {
        return !$this->success;
    }

    public function setFormErrors(FormErrorIterator $formErrors): self
    {
        $this->formErrors = $formErrors;
        return $this;
    }

    public function setPagination(PaginationInterface $pagination): self
    {
        $this->pagination = $pagination;
        return $this;
    }

    public function addMessage($message): self
    {
        $this->messages[] = $message;
        return $this;
    }

    public function setData($data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @throws Exception
     */
    public function setCustom($key, $value): self
    {
        if (in_array($key, ['success', 'messages', 'pagination', 'data'])) {
            throw new Exception("The key '{$key}' is reserved word.");
        }
        $this->custom[$key] = $value;

        return $this;
    }

    private function getErrorsFromForm(FormErrorIterator $formErrors): array
    {
        $errors = [];
        $form = $formErrors->getForm();
        foreach ($form->getErrors() as $error) {
            $errors['errors'] = $error->getMessage();
            $messageParameters = $error->getMessageParameters();
            if (isset($messageParameters['{{ extra_fields }}'])) {
                $errors['extraFields'][] = $messageParameters['{{ extra_fields }}'];
            }
        }
        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = $this->getErrorsFromForm($childForm->getErrors())) {
                    $errors[$childForm->getName()] = $childErrors;
                }
            }
        }
        return $errors;
    }


    public function getResponse(): array
    {
        $response = [
            'success' => $this->success,
        ];
        if (count($this->messages)) {
            $response = array_merge($response, ['messages' => $this->messages]);
        }
        if ($this->pagination !== null) {
            $response = array_merge($response, ['pagination' => self::getPaginationInfo($this->pagination)]);
        }
        if ($this->data !== null) {
            $response = array_merge($response, ['data' => $this->data]);
        }
        if ($this->formErrors !== null) {
            $response = array_merge($response, ['formErrors' => $this->getErrorsFromForm($this->formErrors)]);
        }
        if (count($this->custom)) {
            foreach ($this->custom as $itemKey => $itemValue) {
                $response = array_merge($response, [$itemKey => $itemValue]);
            }
        }
        return $response;
    }


    private static function getPaginationInfo(PaginationInterface $pagination): array
    {
        return [
            'page' => $pagination->getCurrentPageNumber(),
            'pages' => $pagination->getPaginationData()['pageCount'],
            'totalItems' => $pagination->getTotalItemCount(),
            'items' => $pagination->getItems()
        ];
    }

}
