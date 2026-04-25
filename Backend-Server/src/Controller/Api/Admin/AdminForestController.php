<?php

namespace App\Controller\Api\Admin;

use App\Controller\RestController;
use App\Entity\Forest;
use App\Form\ForestType;
use App\Request\ForestRequest;
use App\Service\ForestService;
use App\Service\RestHelperService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/forests')]
class AdminForestController extends RestController
{
    public function __construct(
        RestHelperService $rest,
        EntityManagerInterface $em,
        private readonly ForestService $forestService
    )
    {
        parent::__construct($rest, $em);
    }

    /**
     * Returns Get Forests.
     * @param ForestRequest $forestRequest
     * @return Response
     */
    #[Route('', methods: ['GET'])]
    #[IsGranted('ROLE_FOREST_LIST')]
    #[OA\Tag(name: 'Admin Forest')]
    #[OA\Response(response: 200, description: 'Returns Forests', content: new OA\JsonContent(properties: array(
        new OA\Property(property: "success", type: "boolean"),
        new OA\Property(property: "pagination", properties: array(
            new OA\Property(property: 'page', type: 'integer', example: 1),
            new OA\Property(property: 'pages', type: 'integer', example: 1),
            new OA\Property(property: 'totalItems', type: 'integer', example: 100),
            new OA\Property(property: "items", type: "array",
                items: new OA\Items(
                    ref: new Model(type: Forest::class, groups: array("list"))
                )
            ),
        )),
    )))]
    #[OA\Parameter(ref: "#components/parameters/direction")]
    #[OA\Parameter(ref: "#components/parameters/page")]
    #[OA\Parameter(ref: "#components/parameters/limit")]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    #[OA\Parameter(name: "sort", description: "Sorting results by specified attribute.", in: "query",
        schema: new OA\Schema(type: "string", enum: ["f.id","f.name","f.createdAt"]),
        example: "f.id"
    )]
    #[OA\Parameter(name: "search", description: "Apply filter by name.", in: "query",
        schema: new OA\Schema(type: "string", example: "forest name"),
    )]
    #[Security(name: "Bearer")]
    public function index(ForestRequest $forestRequest): Response
    {
        $page = $forestRequest->getPage();
        $limit = $forestRequest->getLimit();
        $filters = $forestRequest->getFilters();
        $forestRequest->validateSort();

        $pagination = $this->forestService->getList($page, $limit, $filters);
        $this->rest->setPagination($pagination);

        return $this
            ->setGroup(["list"])
            ->viewResponse($this->rest->getResponse(),
                Response::HTTP_OK);
    }

    /**
     * Get Forest By id.
     * @param Forest $forest
     * @return Response
     */
    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted('ROLE_FOREST_SHOW')]
    #[OA\Tag(name: 'Admin Forest')]
    #[OA\Response(response: 200, description: 'Returns Forest By id', content: new OA\JsonContent(properties: [
        new OA\Property(property: "success", type: "boolean"),
        new OA\Property(property: "data", ref: new Model(type: Forest::class, groups: ['details']))
    ]))]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    public function show(Forest $forest): Response
    {
        $this->rest->succeeded()->setData($forest);

        return $this
            ->setGroup(['details'])
            ->viewResponse($this->rest->getResponse(), Response::HTTP_OK);
    }

    /**
     * Create Forest.
     * @param Request $request
     * @return Response
     */
    #[Route('', methods: ['POST'])]
    #[IsGranted('ROLE_FOREST_ADD')]
    #[OA\Tag(name: 'Admin Forest')]
    #[OA\Response(response: 201, description: 'Returns Created Forest', content: new OA\JsonContent(properties: [
        new OA\Property(property: "success", type: "boolean"),
        new OA\Property(property: 'data', ref: new Model(type: Forest::class, groups: ['details']))
    ]))]
    #[OA\RequestBody(
        content: [
            'multipart/form-data' => new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(ref: new Model(type: ForestType::class))
            ),
            'application/json' => new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(ref: new Model(type: ForestType::class))
            ),
        ]
    )]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    public function create(Request $request): Response
    {
        $forest = new Forest();

        $form = $this->createForm(ForestType::class, $forest);

        $this->submitAttempt($form, $request->request->all());
        $this->forestService->add($forest);

        $this->rest->succeeded()->setData($forest);

        return $this
            ->setGroup(['details'])
            ->viewResponse($this->rest->getResponse(), Response::HTTP_CREATED);
    }

    /**
     * Update Forest By id.
     * @param Request $request
     * @param Forest $forest
     * @return Response
     */
    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['PUT'])]
    #[IsGranted('ROLE_FOREST_UPDATE')]
    #[OA\Tag(name: 'Admin Forest')]
    #[OA\Response(response: 201, description: 'Returns Updated Forest.', content: new OA\JsonContent(properties: [
        new OA\Property(property: "success", type: "boolean"),
        new OA\Property(property: 'data', ref: new Model(type: Forest::class, groups: ['details']))
    ]))]
    #[OA\RequestBody(
        content: [
            'multipart/form-data' => new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(ref: new Model(type: ForestType::class))
            ),
            'application/json' => new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(ref: new Model(type: ForestType::class))
            ),
        ]
    )]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    public function update(Request $request, Forest $forest): Response
    {
        $form = $this->createForm(ForestType::class, $forest);

        $this->submitAttempt($form, $request->request->all(), false);

        $this->forestService->update($forest);

        $this->rest->succeeded()->setData($forest);

        return $this
            ->setGroup(['details'])
            ->viewResponse($this->rest->getResponse(), Response::HTTP_OK);
    }

    /**
     * Delete Forest By id.
     * @param Forest $forest
     * @return Response
     */
    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    #[IsGranted('ROLE_FOREST_DELETE')]
    #[OA\Tag(name: 'Admin Forest')]
    #[OA\Response(
        response: 201,
        description: 'Returns Deleted Forest.',
        content: new OA\JsonContent(properties: [
            new OA\Property(property: "success", type: "boolean"),
            new OA\Property(property: 'data', ref: new Model(type: Forest::class, groups: ['details']))
        ])
    )]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    public function delete(Forest $forest): Response
    {
        $this->forestService->delete($forest);

        $this->rest->succeeded()->setData($forest);

        return $this
            ->setGroup(['details'])
            ->viewResponse($this->rest->getResponse(), Response::HTTP_OK);
    }
}