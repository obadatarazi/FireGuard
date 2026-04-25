<?php

namespace App\Controller\Api\Mobile;

use App\Controller\RestController;
use App\Entity\Device;
use App\Request\DeviceRequest;
use App\Service\DeviceService;
use App\Service\RestHelperService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/mobile/fire-brigade/devices')]
class DeviceController extends RestController
{
    public function __construct(
        RestHelperService $rest,
        EntityManagerInterface $em,
        private readonly DeviceService $deviceService
    )
    {
        parent::__construct($rest, $em);
    }

    /**
     * Returns Get Devices.
     * @param DeviceRequest $deviceRequest
     * @return Response
     */
    #[Route('', methods: ['GET'])]
    #[OA\Tag(name: 'Mobile Device')]
    #[OA\Response(response: 200, description: 'Returns Devices', content: new OA\JsonContent(properties: array(
        new OA\Property(property: "success", type: "boolean"),
        new OA\Property(property: "pagination", properties: array(
            new OA\Property(property: 'page', type: 'integer', example: 1),
            new OA\Property(property: 'pages', type: 'integer', example: 1),
            new OA\Property(property: 'totalItems', type: 'integer', example: 100),
            new OA\Property(property: "items", type: "array",
                items: new OA\Items(
                    ref: new Model(type: Device::class, groups: array("list"))
                )
            ),
        )),
    )))]
    #[OA\Parameter(ref: "#components/parameters/direction")]
    #[OA\Parameter(ref: "#components/parameters/page")]
    #[OA\Parameter(ref: "#components/parameters/limit")]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    #[OA\Parameter(name: "sort", description: "Sorting results by specified attribute.", in: "query",
        schema: new OA\Schema(type: "string", enum: ["s.id","s.name","s.createdAt"]),
        example: "s.id"
    )]
    #[OA\Parameter(name: "search", description: "Apply filter by name.", in: "query",
        schema: new OA\Schema(type: "string", example: "Device name"),
    )]
    #[OA\Parameter(name: "forest", description: "Apply filter by forest.", in: "query",
        schema: new OA\Schema(type: "integer", example: 1),
    )]
    #[Security(name: "Bearer")]
    public function index(DeviceRequest $deviceRequest): Response
    {
        $page = $deviceRequest->getPage();
        $limit = $deviceRequest->getLimit();
        $filters = $deviceRequest->getFilters();
        $deviceRequest->validateSort();

        $pagination = $this->deviceService->getList($page, $limit, $filters);
        $this->rest->setPagination($pagination);

        return $this
            ->setGroup(["list", "details"])
            ->viewResponse($this->rest->getResponse(),
                Response::HTTP_OK);
    }

    /**
     * Get Device By id.
     * @param Device $device
     * @return Response
     */
    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[OA\Tag(name: 'Mobile Device')]
    #[OA\Response(response: 200, description: 'Returns Device By id', content: new OA\JsonContent(properties: [
        new OA\Property(property: "success", type: "boolean"),
        new OA\Property(property: "data", ref: new Model(type: Device::class, groups: ['details']))
    ]))]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    public function show(Device $device): Response
    {
        $this->rest->succeeded()->setData($device);

        return $this
            ->setGroup(['details'])
            ->viewResponse($this->rest->getResponse(), Response::HTTP_OK);
    }
}