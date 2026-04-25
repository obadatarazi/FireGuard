<?php

namespace App\Controller\Api\Admin;

use App\Controller\RestController;
use App\Entity\Device;
use App\Form\DeviceType;
use App\Request\DeviceRequest;
use App\Service\DeviceService;
use App\Service\RestHelperService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/devices')]
class AdminDeviceController extends RestController
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
    #[IsGranted('ROLE_DEVICE_LIST')]
    #[OA\Tag(name: 'Admin Device')]
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
            ->setGroup(["list"])
            ->viewResponse($this->rest->getResponse(),
                Response::HTTP_OK);
    }

    /**
     * Get Device By id.
     * @param Device $device
     * @return Response
     */
    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted('ROLE_DEVICE_SHOW')]
    #[OA\Tag(name: 'Admin Device')]
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

    /**
     * Create Device.
     * @param Request $request
     * @return Response
     */
    #[Route('', methods: ['POST'])]
    #[IsGranted('ROLE_DEVICE_ADD')]
    #[OA\Tag(name: 'Admin Device')]
    #[OA\Response(response: 201, description: 'Returns Created Device', content: new OA\JsonContent(properties: [
        new OA\Property(property: "success", type: "boolean"),
        new OA\Property(property: 'data', ref: new Model(type: Device::class, groups: ['details']))
    ]))]
    #[OA\RequestBody(
        content: [
            'multipart/form-data' => new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(ref: new Model(type: DeviceType::class))
            ),
            'application/json' => new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(ref: new Model(type: DeviceType::class))
            ),
        ]
    )]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    public function create(Request $request): Response
    {
        $device = new Device();

        $form = $this->createForm(DeviceType::class, $device);

        $this->submitAttempt($form, $request->request->all());
        $this->deviceService->add($device);

        $this->rest->succeeded()->setData($device);

        return $this
            ->setGroup(['details'])
            ->viewResponse($this->rest->getResponse(), Response::HTTP_CREATED);
    }

    /**
     * Update Device By id.
     * @param Request $request
     * @param Device $device
     * @return Response
     */
    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['PUT'])]
    #[IsGranted('ROLE_DEVICE_UPDATE')]
    #[OA\Tag(name: 'Admin Device')]
    #[OA\Response(response: 201, description: 'Returns Updated Device.', content: new OA\JsonContent(properties: [
        new OA\Property(property: "success", type: "boolean"),
        new OA\Property(property: 'data', ref: new Model(type: Device::class, groups: ['details']))
    ]))]
    #[OA\RequestBody(
        content: [
            'multipart/form-data' => new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(ref: new Model(type: DeviceType::class))
            ),
            'application/json' => new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(ref: new Model(type: DeviceType::class))
            ),
        ]
    )]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    public function update(Request $request, Device $device): Response
    {
        $form = $this->createForm(DeviceType::class, $device);

        $this->submitAttempt($form, $request->request->all(), false);

        $this->deviceService->update($device);

        $this->rest->succeeded()->setData($device);

        return $this
            ->setGroup(['details'])
            ->viewResponse($this->rest->getResponse(), Response::HTTP_OK);
    }

    /**
     * Delete Device By id.
     * @param Device $device
     * @return Response
     */
    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    #[IsGranted('ROLE_DEVICE_DELETE')]
    #[OA\Tag(name: 'Admin Device')]
    #[OA\Response(
        response: 201,
        description: 'Returns Deleted Device.',
        content: new OA\JsonContent(properties: [
            new OA\Property(property: "success", type: "boolean"),
            new OA\Property(property: 'data', ref: new Model(type: Device::class, groups: ['details']))
        ])
    )]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    public function delete(Device $device): Response
    {
        $this->deviceService->delete($device);

        $this->rest->succeeded()->setData($device);

        return $this
            ->setGroup(['details'])
            ->viewResponse($this->rest->getResponse(), Response::HTTP_OK);
    }
}