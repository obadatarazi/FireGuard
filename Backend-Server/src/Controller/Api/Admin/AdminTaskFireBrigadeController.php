<?php

namespace App\Controller\Api\Admin;

use App\Controller\RestController;
use App\Entity\TaskFireBrigade;
use App\Form\TaskFireBrigadeType;
use App\Request\TaskFireBrigadeRequest;
use App\Service\TaskFireBrigadeService;
use App\Service\RestHelperService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/task-fire-brigades')]
class AdminTaskFireBrigadeController extends RestController
{
    public function __construct(
        RestHelperService $rest,
        EntityManagerInterface $em,
        private readonly TaskFireBrigadeService $taskFireBrigadeService
    )
    {
        parent::__construct($rest, $em);
    }

    /**
    * Returns Get Task Fire Brigades.
    * @param TaskFireBrigadeRequest $taskFireBrigadeRequest
    * @return Response
    */
    #[Route('', methods: ['GET'])]
    #[IsGranted('ROLE_TASK_FIRE_BRIGADE_LIST')]
    #[OA\Tag(name: 'Admin Task Fire Brigade')]
    #[OA\Response(response: 200, description: 'Returns Task Fire Brigades', content: new OA\JsonContent(properties: array(
        new OA\Property(property: "success", type: "boolean"),
        new OA\Property(property: "pagination", properties: array(
            new OA\Property(property: 'page', type: 'integer', example: 1),
            new OA\Property(property: 'pages', type: 'integer', example: 1),
            new OA\Property(property: 'totalItems', type: 'integer', example: 100),
            new OA\Property(property: "items", type: "array",
                items: new OA\Items(
                    ref: new Model(type: TaskFireBrigade::class, groups: array("list"))
                )
            ),
        )),
    )))]
    #[OA\Parameter(ref: "#components/parameters/direction")]
    #[OA\Parameter(ref: "#components/parameters/page")]
    #[OA\Parameter(ref: "#components/parameters/limit")]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    #[OA\Parameter(name: "sort", description: "Sorting results by specified attribute.", in: "query",
        schema: new OA\Schema(type: "string", enum: ["tf.id","tf.fire","tf.createdAt"]),
        example: "tf.id"
    )]
    #[OA\Parameter(name: "fire", description: "Apply filter by fire.", in: "query",
        schema: new OA\Schema(type: "integer", example: 1),
    )]
    #[OA\Parameter(name: "fireBrigade", description: "Apply filter by fireBrigade.", in: "query",
    schema: new OA\Schema(type: "integer", example: 1),
    )]
    #[Security(name: "Bearer")]
    public function index(TaskFireBrigadeRequest $taskFireBrigadeRequest): Response
    {
        $page = $taskFireBrigadeRequest->getPage();
        $limit = $taskFireBrigadeRequest->getLimit();
        $filters = $taskFireBrigadeRequest->getFilters();
        $taskFireBrigadeRequest->validateSort();

        $pagination = $this->taskFireBrigadeService->getList($page, $limit, $filters);
        $this->rest->setPagination($pagination);

        return $this
            ->setGroup(["list"])
            ->viewResponse($this->rest->getResponse(),
                Response::HTTP_OK);
    }

    /**
    * Get Task Fire Brigade By id.
    * @param TaskFireBrigade $taskFireBrigade
    * @return Response
    */
    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted('ROLE_TASK_FIRE_BRIGADE_SHOW')]
    #[OA\Tag(name: 'Admin Task Fire Brigade')]
    #[OA\Response(response: 200, description: 'Returns Task Fire Brigade By id', content: new OA\JsonContent(properties: [
        new OA\Property(property: "success", type: "boolean"),
        new OA\Property(property: "data", ref: new Model(type: TaskFireBrigade::class, groups: ['details']))
    ]))]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    public function show(TaskFireBrigade $taskFireBrigade): Response
    {
        $this->rest->succeeded()->setData($taskFireBrigade);

        return $this
            ->setGroup(['details'])
            ->viewResponse($this->rest->getResponse(), Response::HTTP_OK);
    }

    /**
    * Create Task Fire Brigade.
    * @param Request $request
    * @return Response
    */
    #[Route('', methods: ['POST'])]
    #[IsGranted('ROLE_TASK_FIRE_BRIGADE_ADD')]
    #[OA\Tag(name: 'Admin Task Fire Brigade')]
    #[OA\Response(response: 201, description: 'Returns Created Task Fire Brigade', content: new OA\JsonContent(properties: [
        new OA\Property(property: "success", type: "boolean"),
        new OA\Property(property: 'data', ref: new Model(type: TaskFireBrigade::class, groups: ['details']))
    ]))]
    #[OA\RequestBody(
        content: [
            'multipart/form-data' => new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(ref: new Model(type: TaskFireBrigadeType::class))
            ),
            'application/json' => new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(ref: new Model(type: TaskFireBrigadeType::class))
            ),
        ]
    )]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    public function create(Request $request): Response
    {
        $taskFireBrigade = new TaskFireBrigade();

        $form = $this->createForm(TaskFireBrigadeType::class, $taskFireBrigade);

        $this->submitAttempt($form, $request->request->all());
        $this->taskFireBrigadeService->add($taskFireBrigade);

        $this->rest->succeeded()->setData($taskFireBrigade);

        return $this
            ->setGroup(['details'])
            ->viewResponse($this->rest->getResponse(), Response::HTTP_CREATED);
    }

    /**
    * Update Task Fire Brigade By id.
    * @param Request $request
    * @param TaskFireBrigade $taskFireBrigade
    * @return Response
    */
    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['PUT'])]
    #[IsGranted('ROLE_TASK_FIRE_BRIGADE_UPDATE')]
    #[OA\Tag(name: 'Admin Task Fire Brigade')]
    #[OA\Response(response: 201, description: 'Returns Updated Task Fire Brigade.', content: new OA\JsonContent(properties: [
        new OA\Property(property: "success", type: "boolean"),
        new OA\Property(property: 'data', ref: new Model(type: TaskFireBrigade::class, groups: ['details']))
    ]))]
    #[OA\RequestBody(
        content: [
            'multipart/form-data' => new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(ref: new Model(type: TaskFireBrigadeType::class))
            ),
            'application/json' => new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(ref: new Model(type: TaskFireBrigadeType::class))
            ),
        ]
    )]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    public function update(Request $request, TaskFireBrigade $taskFireBrigade): Response
    {
        $form = $this->createForm(TaskFireBrigadeType::class, $taskFireBrigade);

        $this->submitAttempt($form, $request->request->all(), false);

        $this->taskFireBrigadeService->update($taskFireBrigade);

        $this->rest->succeeded()->setData($taskFireBrigade);

        return $this
            ->setGroup(['details'])
            ->viewResponse($this->rest->getResponse(), Response::HTTP_OK);
    }

    /**
    * Delete Task Fire Brigade By id.
    * @param TaskFireBrigade $taskFireBrigade
    * @return Response
    */
    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    #[IsGranted('ROLE_TASK_FIRE_BRIGADE_DELETE')]
    #[OA\Tag(name: 'Admin Task Fire Brigade')]
    #[OA\Response(
        response: 201,
        description: 'Returns Deleted Task Fire Brigade.',
        content: new OA\JsonContent(properties: [
            new OA\Property(property: "success", type: "boolean"),
            new OA\Property(property: 'data', ref: new Model(type: TaskFireBrigade::class, groups: ['details']))
        ])
    )]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    public function delete(TaskFireBrigade $taskFireBrigade): Response
    {
        $this->taskFireBrigadeService->delete($taskFireBrigade);

        $this->rest->succeeded()->setData($taskFireBrigade);

        return $this
            ->setGroup(['details'])
            ->viewResponse($this->rest->getResponse(), Response::HTTP_OK);
    }
}