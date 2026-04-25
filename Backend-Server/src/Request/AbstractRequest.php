<?php

namespace App\Request;

use App\Service\ValidationService;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

abstract class AbstractRequest
{
    private readonly InputBag $queries;
    private array $filters;

    private readonly ?string $sort;
    private readonly ?string $direction;
    private readonly ?string $page;
    private readonly ?string $limit;

    private ValidationService $validationService;
    private RequestStack $requestStack;

    public function __construct(ValidationService $validationService, RequestStack $requestStack)
    {
        $this->validationService = $validationService;
        $this->requestStack = $requestStack;
        $this->filters = [];
        $this->queries = $this->getRequest()->query;
        $this->sort = $this->queries->get('sort');
        $this->direction = $this->queries->get('direction');
        $this->page = $this->queries->get('page');
        $this->limit = $this->queries->get('limit');
    }

    public function getRequest(): Request
    {
        return $this->requestStack->getCurrentRequest();
    }

    public function getFilters(): array
    {
        if (count($this->filters) > 0) {
            return $this->filters;
        }

        foreach ($this->defineFilters() as $filter) {
            $key = $filter['key'];
            $type = $filter['type'];
            $value = $this->queries->get($key);
            if ($value == null) {
                $this->filters[$key] = null;
            } else {
                $this->filters[$key] = $this->validationService->validate($value, $type);
            }

        }

        return $this->filters;
    }

    public function getSort(): array
    {
        $default = $this->getDefaultSort();

        $direction = $this->validationService->inArrayOrPut(
            $this->direction,
            ['Asc', 'asc', 'Desc', 'desc'],
            $default['direction']
        );

        $sort = $this->validationService->inArrayOrPut(
            $this->sort,
            $this->sortableField(),
            $default['sort']
        );

        return [
            'sort' => $sort,
            'direction' => $direction,
        ];
    }

    public function validateSort(): void
    {
        $sort = $this->getSort();

        $this->getRequest()->query->set('sort', $sort['sort']);
        $this->getRequest()->query->set('direction', $sort['direction']);
    }

    public function getLimit($default = 20): int
    {
        return $this->validationService->inArrayOrPut(
            $this->limit,
            [10, 12, 20, 30, 40, 50],
            $default);
    }

    public function getPage(): int
    {
        return $this->validationService->parseIntOrPut(
            $this->page, 1);
    }

    protected function getDefaultSort(): array
    {
        $defaultDirection = 'Desc';

        foreach ($this->sortableField() as $sort) {
            $split = explode(".", $sort);
            if (count($split) > 1 && $split[1] == 'id') {
                return [
                    'sort' => $sort,
                    'direction' => $defaultDirection,
                ];
            }
        }
        return [
            'sort' => "",
            'direction' => "",
        ];
    }

    abstract protected function defineFilters(): array;

    abstract protected function sortableField(): array;
}
