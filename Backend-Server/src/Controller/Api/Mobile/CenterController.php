<?php

namespace App\Controller\Api\Mobile;

use App\Controller\RestController;
use App\Entity\Center;
use App\Request\CenterRequest;
use App\Service\CenterService;
use App\Service\RestHelperService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/mobile/fire-brigade/centers')]

class CenterController extends RestController
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
    #[OA\Tag(name: 'Mobile Center')]
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
    #[OA\Tag(name: 'Mobile Center')]
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
}