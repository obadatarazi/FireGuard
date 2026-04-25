<?php

namespace App\Controller\Api\Mobile;

use App\Controller\RestController;
use App\Entity\FireBrigade;
use App\Entity\MobileSensor;
use App\Request\MobileSensorRequest;
use App\Service\MobileSensorService;
use App\Service\RestHelperService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/mobile/fire-brigade/mobile-sensors')]
class MobileSensorController extends RestController
{
    public function __construct(
        RestHelperService $rest,
        EntityManagerInterface $em,
        private readonly MobileSensorService $mobileSensorService
    ) {
        parent::__construct($rest, $em);
    }

    #[Route('', methods: ['GET'])]
    #[OA\Tag(name: 'Mobile Mobile Sensor')]
    #[OA\Response(response: 200, description: 'Returns Mobile Sensors for my center', content: new OA\JsonContent(properties: array(
        new OA\Property(property: "success", type: "boolean"),
        new OA\Property(property: "pagination", properties: array(
            new OA\Property(property: 'page', type: 'integer', example: 1),
            new OA\Property(property: 'pages', type: 'integer', example: 1),
            new OA\Property(property: 'totalItems', type: 'integer', example: 0),
            new OA\Property(property: "items", type: "array",
                items: new OA\Items(
                    ref: new Model(type: MobileSensor::class, groups: array("list"))
                )
            ),
        )),
    )))]
    #[OA\Parameter(ref: "#components/parameters/direction")]
    #[OA\Parameter(ref: "#components/parameters/page")]
    #[OA\Parameter(ref: "#components/parameters/limit")]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    #[OA\Parameter(name: "sort", description: "Sorting results by specified attribute.", in: "query",
        schema: new OA\Schema(type: "string", enum: ["ms.id","ms.createdAt","ms.name","ms.type","ms.status"]),
        example: "ms.id"
    )]
    #[OA\Parameter(name: "type", description: "Apply filter by type. Allowed `value` values from GET /api/options/mobile-sensors/types.", in: "query",
        schema: new OA\Schema(type: "string", enum: [MobileSensor::TYPE_DRONE, MobileSensor::TYPE_CAR_ROBOT], example: MobileSensor::TYPE_DRONE),
    )]
    #[OA\Parameter(name: "status", description: "Apply filter by status.", in: "query",
        schema: new OA\Schema(type: "string", example: "ACTIVE"),
    )]
    #[OA\Parameter(name: "search", description: "Apply filter by name.", in: "query",
        schema: new OA\Schema(type: "string", example: "Drone"),
    )]
    #[Security(name: "Bearer")]
    public function index(MobileSensorRequest $mobileSensorRequest): Response
    {
        /** @var FireBrigade $me */
        $me = $this->getUser();
        $center = $me?->getCenter();

        $page = $mobileSensorRequest->getPage();
        $limit = $mobileSensorRequest->getLimit();
        $filters = $mobileSensorRequest->getFilters();
        $mobileSensorRequest->validateSort();

        // Force scope to my center (ignore any client-supplied center).
        $filters['center'] = $center?->getId();

        $pagination = $this->mobileSensorService->getList($page, $limit, $filters);
        $this->rest->setPagination($pagination);

        return $this
            ->setGroup(["list"])
            ->viewResponse($this->rest->getResponse(), Response::HTTP_OK);
    }
}

