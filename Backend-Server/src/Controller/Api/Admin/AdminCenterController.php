<?php

namespace App\Controller\Api\Admin;

use App\Controller\RestController;
use App\Entity\Center;
use App\Form\CenterType;
use App\Request\CenterRequest;
use App\Service\CenterService;
use App\Service\RestHelperService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/centers')]

class AdminCenterController extends RestController
{
    public function __construct(
        RestHelperService $rest,
        EntityManagerInterface $em,
        private readonly CenterService $centerService
    ) {
        parent::__construct($rest, $em);
    }

    /**
     * Returns Get Centers.
     * @param CenterRequest $centerRequest
     * @return Response
     */
    #[Route('', methods: ['GET'])]
    #[IsGranted('ROLE_CENTER_LIST')]
    #[OA\Tag(name: 'Admin Center')]
    #[OA\Response(response: 200, description: 'Returns Centers', content: new OA\JsonContent(
        properties: array(
            new OA\Property(property: "success", type: "boolean"),
            new OA\Property(
                property: "pagination",
                properties: array(
                    new OA\Property(property: 'page', type: 'integer', example: 1),
                    new OA\Property(property: 'pages', type: 'integer', example: 1),
                    new OA\Property(property: 'totalItems', type: 'integer', example: 100),
                    new OA\Property(
                        property: "items",
                        type: "array",
                        items: new OA\Items(
                            ref: new Model(type: Center::class, groups: array("list"))
                        )
                    ),
                )
            ),
        )
    )
    )]
    #[OA\Parameter(ref: "#components/parameters/direction")]
    #[OA\Parameter(ref: "#components/parameters/page")]
    #[OA\Parameter(ref: "#components/parameters/limit")]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    #[OA\Parameter(name: "sort", description: "Sorting results by specified attribute.", in: "query",
        schema: new OA\Schema(type: "string", enum: ["c.id", "c.name", "c.createdAt"]),
        example: "c.id"
    )]
    #[OA\Parameter(name: "search", description: "Apply filter by name.", in: "query",
        schema: new OA\Schema(type: "string", example: "center name"),
    )]
    #[OA\Parameter(name: "phoneNumber", description: "Apply filter by phoneNumber.", in: "query",
        schema: new OA\Schema(type: "string", example: "0933514147"),
    )]
    #[OA\Parameter(name: "status", description: "Apply filter by status.", in: "query",
        schema: new OA\Schema(type: "string", example: "status"),
    )]
    #[Security(name: "Bearer")]
    public function index(CenterRequest $centerRequest): Response
    {
        $page = $centerRequest->getPage();
        $limit = $centerRequest->getLimit();
        $filters = $centerRequest->getFilters();
        $centerRequest->validateSort();

        $pagination = $this->centerService->getList($page, $limit, $filters);
        $this->rest->setPagination($pagination);

        return $this
            ->setGroup(["list"])
            ->viewResponse(
                $this->rest->getResponse(),
                Response::HTTP_OK
            );
    }

    /**
     * Get Center By id.
     * @param Center $center
     * @return Response
     */
    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted('ROLE_CENTER_SHOW')]
    #[OA\Tag(name: 'Admin Center')]
    #[OA\Response(response: 200, description: 'Returns Center By id', content: new OA\JsonContent(properties: [
        new OA\Property(property: "success", type: "boolean"),
        new OA\Property(property: "data", ref: new Model(type: Center::class, groups: ['details']))
    ]))]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    public function show(Center $center): Response
    {
        $this->rest->succeeded()->setData($center);

        return $this
            ->setGroup(['details'])
            ->viewResponse($this->rest->getResponse(), Response::HTTP_OK);
    }

    /**
     * Create Center.
     * @param Request $request
     * @return Response
     */
    #[Route('', methods: ['POST'])]
    #[IsGranted('ROLE_CENTER_ADD')]
    #[OA\Tag(name: 'Admin Center')]
    #[OA\Response(response: 201, description: 'Returns Created Center', content: new OA\JsonContent(properties: [
        new OA\Property(property: "success", type: "boolean"),
        new OA\Property(property: 'data', ref: new Model(type: Center::class, groups: ['details']))
    ]))]
    #[OA\RequestBody(
        content: [
            'multipart/form-data' => new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    ref: new Model(type: CenterType::class)
                )
            ),
            'application/json' => new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    ref: new Model(type: CenterType::class)
                )
            ),
        ]
    )]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    public function create(Request $request): Response
    {
        $center = new Center();

        $form = $this->createForm(CenterType::class, $center);

        $this->submitAttempt($form, $request->request->all());

        $this->centerService->add($center);

        $this->rest->succeeded()->setData($center);

        return $this
            ->setGroup(['details'])
            ->viewResponse($this->rest->getResponse(), Response::HTTP_CREATED);
    }

    /**
     * Update Center By id.
     * @param Request $request
     * @param Center $center
     * @return Response
     */
    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['PUT'])]
    #[IsGranted('ROLE_CENTER_UPDATE')]
    #[OA\Tag(name: 'Admin Center')]
    #[OA\Response(response: 201, description: 'Returns Updated Center.', content: new OA\JsonContent(properties: [
        new OA\Property(property: "success", type: "boolean"),
        new OA\Property(property: 'data', ref: new Model(type: Center::class, groups: ['details']))
    ]))]
    #[OA\RequestBody(
        content: [
            'multipart/form-data' => new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    ref: new Model(type: CenterType::class)
                )
            ),
            'application/json' => new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    ref: new Model(type: CenterType::class)
                )
            ),
        ]
    )]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    public function update(Request $request, Center $center): Response
    {
        $form = $this->createForm(CenterType::class, $center);

        $this->submitAttempt($form, $request->request->all(), false);

        $this->centerService->update($center);

        $this->rest->succeeded()->setData($center);

        return $this
            ->setGroup(['details'])
            ->viewResponse($this->rest->getResponse(), Response::HTTP_OK);
    }

    /**
     * Delete Center By id.
     * @param Center $center
     * @return Response
     */
    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    #[IsGranted('ROLE_CENTER_DELETE')]
    #[OA\Tag(name: 'Admin Center')]
    #[OA\Response(
        response: 201,
        description: 'Returns Deleted Center.',
        content: new OA\JsonContent(properties: [
            new OA\Property(property: "success", type: "boolean"),
            new OA\Property(property: 'data', ref: new Model(type: Center::class, groups: ['details']))
        ])
    )]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    public function delete(Center $center): Response
    {
        $this->centerService->delete($center);

        $this->rest->succeeded()->setData($center);

        return $this
            ->setGroup(['details'])
            ->viewResponse($this->rest->getResponse(), Response::HTTP_OK);
    }
}