<?php

namespace App\Controller\Api\Admin;

use App\Controller\RestController;
use App\Entity\MobileSensor;
use App\Form\MobileSensorType;
use App\Request\MobileSensorRequest;
use App\Service\MobileSensorService;
use App\Service\RestHelperService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/mobile-sensors')]
class AdminMobileSensorController extends RestController
{
    public function __construct(
        RestHelperService $rest,
        EntityManagerInterface $em,
        private readonly MobileSensorService $mobileSensorService
    ) {
        parent::__construct($rest, $em);
    }

    #[Route('', methods: ['GET'])]
    #[OA\Tag(name: 'Admin Mobile Sensor')]
    #[OA\Response(response: 200, description: 'Returns Mobile Sensors', content: new OA\JsonContent(properties: array(
        new OA\Property(property: "success", type: "boolean"),
        new OA\Property(property: "pagination", properties: array(
            new OA\Property(property: 'page', type: 'integer', example: 1),
            new OA\Property(property: 'pages', type: 'integer', example: 1),
            new OA\Property(property: 'totalItems', type: 'integer', example: 100),
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
    #[OA\Parameter(name: "center", description: "Apply filter by center.", in: "query",
        schema: new OA\Schema(type: "integer", example: 1),
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
        $page = $mobileSensorRequest->getPage();
        $limit = $mobileSensorRequest->getLimit();
        $filters = $mobileSensorRequest->getFilters();
        $mobileSensorRequest->validateSort();

        $pagination = $this->mobileSensorService->getList($page, $limit, $filters);
        $this->rest->setPagination($pagination);

        return $this
            ->setGroup(["list"])
            ->viewResponse($this->rest->getResponse(), Response::HTTP_OK);
    }

    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[OA\Tag(name: 'Admin Mobile Sensor')]
    #[OA\Response(response: 200, description: 'Returns Mobile Sensor By id', content: new OA\JsonContent(properties: [
        new OA\Property(property: "success", type: "boolean"),
        new OA\Property(property: "data", ref: new Model(type: MobileSensor::class, groups: ['details']))
    ]))]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    #[Security(name: "Bearer")]
    public function show(MobileSensor $mobileSensor): Response
    {
        $this->rest->succeeded()->setData($mobileSensor);

        return $this
            ->setGroup(['details'])
            ->viewResponse($this->rest->getResponse(), Response::HTTP_OK);
    }

    #[Route('', methods: ['POST'])]
    #[OA\Tag(name: 'Admin Mobile Sensor')]
    #[OA\Response(response: 201, description: 'Returns Created Mobile Sensor', content: new OA\JsonContent(properties: [
        new OA\Property(property: "success", type: "boolean"),
        new OA\Property(property: 'data', ref: new Model(type: MobileSensor::class, groups: ['details']))
    ]))]
    #[OA\RequestBody(
        content: [
            'multipart/form-data' => new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(ref: '#/components/schemas/MobileSensorForm')
            ),
            'application/json' => new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(ref: '#/components/schemas/MobileSensorForm')
            ),
        ]
    )]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    #[Security(name: "Bearer")]
    public function create(Request $request): Response
    {
        $mobileSensor = new MobileSensor();

        $form = $this->createForm(MobileSensorType::class, $mobileSensor);
        $this->submitAttempt($form, array_merge($request->request->all(), $request->files->all()));

        $this->mobileSensorService->add($mobileSensor);
        $this->rest->succeeded()->setData($mobileSensor);

        return $this
            ->setGroup(['details'])
            ->viewResponse($this->rest->getResponse(), Response::HTTP_CREATED);
    }

    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['PUT'])]
    #[OA\Tag(name: 'Admin Mobile Sensor')]
    #[OA\Response(response: 201, description: 'Returns Updated Mobile Sensor.', content: new OA\JsonContent(properties: [
        new OA\Property(property: "success", type: "boolean"),
        new OA\Property(property: 'data', ref: new Model(type: MobileSensor::class, groups: ['details']))
    ]))]
    #[OA\RequestBody(
        content: [
            'multipart/form-data' => new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(ref: '#/components/schemas/MobileSensorForm')
            ),
            'application/json' => new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(ref: '#/components/schemas/MobileSensorForm')
            ),
        ]
    )]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    #[Security(name: "Bearer")]
    public function update(Request $request, MobileSensor $mobileSensor): Response
    {
        $form = $this->createForm(MobileSensorType::class, $mobileSensor);
        $this->submitAttempt($form, array_merge($request->request->all(), $request->files->all()), false);

        $this->mobileSensorService->update($mobileSensor);
        $this->rest->succeeded()->setData($mobileSensor);

        return $this
            ->setGroup(['details'])
            ->viewResponse($this->rest->getResponse(), Response::HTTP_OK);
    }

    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    #[OA\Tag(name: 'Admin Mobile Sensor')]
    #[OA\Response(response: 201, description: 'Returns Deleted Mobile Sensor.', content: new OA\JsonContent(properties: [
        new OA\Property(property: "success", type: "boolean"),
        new OA\Property(property: 'data', ref: new Model(type: MobileSensor::class, groups: ['details']))
    ]))]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    #[Security(name: "Bearer")]
    public function delete(MobileSensor $mobileSensor): Response
    {
        $this->mobileSensorService->remove($mobileSensor);
        $this->rest->succeeded()->setData($mobileSensor);

        return $this
            ->setGroup(['details'])
            ->viewResponse($this->rest->getResponse(), Response::HTTP_OK);
    }
}

