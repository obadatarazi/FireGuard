<?php

namespace App\Controller\Api\Admin;

use App\Constant\FireStatusType;
use App\Controller\RestController;
use App\Entity\Fire;
use App\Form\FireType;
use App\Request\FireRequest;
use App\Service\FireService;
use App\Service\RestHelperService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/fires')]
class AdminFireController extends RestController
{
    public function __construct(
        RestHelperService $rest,
        EntityManagerInterface $em,
        private readonly FireService $fireService
    )
    {
        parent::__construct($rest, $em);
    }

    /**
     * Returns Get Fires.
     * @param FireRequest $fireRequest
     * @return Response
     */
    #[Route('', methods: ['GET'])]
    #[IsGranted('ROLE_FIRE_LIST')]
    #[OA\Tag(name: 'Admin Fire')]
    #[OA\Response(response: 200, description: 'Returns Fires', content: new OA\JsonContent(properties: array(
        new OA\Property(property: "success", type: "boolean"),
        new OA\Property(property: "pagination", properties: array(
            new OA\Property(property: 'page', type: 'integer', example: 1),
            new OA\Property(property: 'pages', type: 'integer', example: 1),
            new OA\Property(property: 'totalItems', type: 'integer', example: 100),
            new OA\Property(property: "items", type: "array",
                items: new OA\Items(
                    ref: new Model(type: Fire::class, groups: array("list"))
                )
            ),
        )),
    )))]
    #[OA\Parameter(ref: "#components/parameters/direction")]
    #[OA\Parameter(ref: "#components/parameters/page")]
    #[OA\Parameter(ref: "#components/parameters/limit")]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    #[OA\Parameter(name: "sort", description: "Sorting results by specified attribute.", in: "query",
        schema: new OA\Schema(type: "string", enum: ["fi.id","fi.forest","fi.createdAt"]),
        example: "fi.id"
    )]
    #[OA\Parameter(name: "forest", description: "Apply filter by forest.", in: "query",
        schema: new OA\Schema(type: "integer", example: 1),
    )]
    #[OA\Parameter(name: "status", description: "Apply filter by status. Allowed `value` values from GET /api/options/fires/status/types.", in: "query",
        schema: new OA\Schema(type: "string", enum: FireStatusType::VALUES, example: "INPROGRESS"),
    )]
    #[OA\Parameter(name: "device", description: "Apply filter by device.", in: "query",
        schema: new OA\Schema(type: "integer", example: 1),
    )]
    #[Security(name: "Bearer")]
    public function index(FireRequest $fireRequest): Response
    {
        $page = $fireRequest->getPage();
        $limit = $fireRequest->getLimit();
        $filters = $fireRequest->getFilters();
        $fireRequest->validateSort();

        $pagination = $this->fireService->getList($page, $limit, $filters);
        $this->rest->setPagination($pagination);

        return $this
            ->setGroup(["list"])
            ->viewResponse($this->rest->getResponse(),
                Response::HTTP_OK);
    }

    /**
     * Get Fire By id.
     * @param Fire $fire
     * @return Response
     */
    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted('ROLE_FIRE_SHOW')]
    #[OA\Tag(name: 'Admin Fire')]
    #[OA\Response(response: 200, description: 'Returns Fire By id', content: new OA\JsonContent(properties: [
        new OA\Property(property: "success", type: "boolean"),
        new OA\Property(property: "data", ref: new Model(type: Fire::class, groups: ['details']))
    ]))]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    public function show(Fire $fire): Response
    {
        $this->rest->succeeded()->setData($fire);

        return $this
            ->setGroup(['details'])
            ->viewResponse($this->rest->getResponse(), Response::HTTP_OK);
    }

    /**
     * Create Fire.
     * @param Request $request
     * @return Response
     */
    #[Route('', methods: ['POST'])]
    #[IsGranted('ROLE_FIRE_ADD')]
    #[OA\Tag(name: 'Admin Fire')]
    #[OA\Response(response: 201, description: 'Returns Created Fire', content: new OA\JsonContent(properties: [
        new OA\Property(property: "success", type: "boolean"),
        new OA\Property(property: 'data', ref: new Model(type: Fire::class, groups: ['details']))
    ]))]
    #[OA\RequestBody(
        content: [
            'multipart/form-data' => new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(ref: new Model(type: FireType::class))
            ),
            'application/json' => new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(ref: new Model(type: FireType::class))
            ),
        ]
    )]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    public function create(Request $request): Response
    {
        $fire = new Fire();

        $form = $this->createForm(FireType::class, $fire);

        $this->submitAttempt($form, $request->request->all());
        $this->fireService->add($fire);

        $this->rest->succeeded()->setData($fire);

        return $this
            ->setGroup(['details'])
            ->viewResponse($this->rest->getResponse(), Response::HTTP_CREATED);
    }

    /**
     * Update Fire By id.
     * @param Request $request
     * @param Fire $fire
     * @return Response
     */
    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['PUT'])]
    #[IsGranted('ROLE_FIRE_UPDATE')]
    #[OA\Tag(name: 'Admin Fire')]
    #[OA\Response(response: 201, description: 'Returns Updated Fire.', content: new OA\JsonContent(properties: [
        new OA\Property(property: "success", type: "boolean"),
        new OA\Property(property: 'data', ref: new Model(type: Fire::class, groups: ['details']))
    ]))]
    #[OA\RequestBody(
        content: [
            'multipart/form-data' => new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(ref: new Model(type: FireType::class))
            ),
            'application/json' => new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(ref: new Model(type: FireType::class))
            ),
        ]
    )]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    public function update(Request $request, Fire $fire): Response
    {
        $form = $this->createForm(FireType::class, $fire);

        $this->submitAttempt($form, $request->request->all(), false);

        $this->fireService->update($fire);

        $this->rest->succeeded()->setData($fire);

        return $this
            ->setGroup(['details'])
            ->viewResponse($this->rest->getResponse(), Response::HTTP_OK);
    }

    /**
     * Delete Fire By id.
     * @param Fire $fire
     * @return Response
     */
    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    #[IsGranted('ROLE_FIRE_DELETE')]
    #[OA\Tag(name: 'Admin Fire')]
    #[OA\Response(
        response: 201,
        description: 'Returns Deleted Fire.',
        content: new OA\JsonContent(properties: [
            new OA\Property(property: "success", type: "boolean"),
            new OA\Property(property: 'data', ref: new Model(type: Fire::class, groups: ['details']))
        ])
    )]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    public function delete(Fire $fire): Response
    {
        $this->fireService->delete($fire);

        $this->rest->succeeded()->setData($fire);

        return $this
            ->setGroup(['details'])
            ->viewResponse($this->rest->getResponse(), Response::HTTP_OK);
    }
}