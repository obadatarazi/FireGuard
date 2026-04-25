<?php

namespace App\Controller\Api\Mobile;

use App\Controller\RestController;
use App\Entity\Forest;
use App\Request\ForestRequest;
use App\Service\ForestService;
use App\Service\RestHelperService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/mobile/fire-brigade/forests')]
class ForestController extends RestController
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
    #[OA\Tag(name: 'Mobile Forest')]
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
    #[OA\Tag(name: 'Mobile Forest')]
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
}