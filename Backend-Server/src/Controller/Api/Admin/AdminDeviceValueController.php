<?php

namespace App\Controller\Api\Admin;

use App\Controller\RestController;
use App\Entity\DeviceValue;
use App\Form\DeviceValueType;
use App\Request\DeviceValueRequest;
use App\Service\DeviceValueService;
use App\Service\RestHelperService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/device-values')]
class AdminDeviceValueController extends RestController
{
    public function __construct(
        RestHelperService $rest,
        EntityManagerInterface $em,
        private readonly DeviceValueService $deviceValueService
    )
    {
        parent::__construct($rest, $em);
    }

    /**
    * Returns Get Device Values.
    * @param DeviceValueRequest $deviceValueRequest
    * @return Response
    */
    #[Route('', methods: ['GET'])]
    #[IsGranted('ROLE_DEVICE_VALUE_LIST')]
    #[OA\Tag(name: 'Admin Device Value')]
    #[OA\Response(response: 200, description: 'Returns Device Values', content: new OA\JsonContent(properties: array(
        new OA\Property(property: "success", type: "boolean"),
        new OA\Property(property: "pagination", properties: array(
            new OA\Property(property: 'page', type: 'integer', example: 1),
            new OA\Property(property: 'pages', type: 'integer', example: 1),
            new OA\Property(property: 'totalItems', type: 'integer', example: 100),
            new OA\Property(property: "items", type: "array",
                items: new OA\Items(
                    ref: new Model(type: DeviceValue::class, groups: array("list"))
                )
            ),
        )),
    )))]
    #[OA\Parameter(ref: "#components/parameters/direction")]
    #[OA\Parameter(ref: "#components/parameters/page")]
    #[OA\Parameter(ref: "#components/parameters/limit")]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    #[OA\Parameter(name: "sort", description: "Sorting results by specified attribute.", in: "query",
        schema: new OA\Schema(type: "string", enum: ["sv.id","sv.device","sv.createdAt"]),
        example: "sv.id"
    )]
    #[OA\Parameter(name: "device", description: "Apply filter by device.", in: "query",
        schema: new OA\Schema(type: "integer", example: 1),
    )]
    #[Security(name: "Bearer")]
    public function index(DeviceValueRequest $deviceValueRequest): Response
    {
        $page = $deviceValueRequest->getPage();
        $limit = $deviceValueRequest->getLimit();
        $filters = $deviceValueRequest->getFilters();
        $deviceValueRequest->validateSort();

        $pagination = $this->deviceValueService->getList($page, $limit, $filters);
        $this->rest->setPagination($pagination);

        return $this
            ->setGroup(["list"])
            ->viewResponse($this->rest->getResponse(),
                Response::HTTP_OK);
    }

    /**
    * Get Device Value By id.
    * @param DeviceValue $deviceValue
    * @return Response
    */
    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted('ROLE_DEVICE_VALUE_SHOW')]
    #[OA\Tag(name: 'Admin Device Value')]
    #[OA\Response(response: 200, description: 'Returns Device Value By id', content: new OA\JsonContent(properties: [
        new OA\Property(property: "success", type: "boolean"),
        new OA\Property(property: "data", ref: new Model(type: DeviceValue::class, groups: ['details']))
    ]))]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    public function show(DeviceValue $deviceValue): Response
    {
        $this->rest->succeeded()->setData($deviceValue);

        return $this
            ->setGroup(['details'])
            ->viewResponse($this->rest->getResponse(), Response::HTTP_OK);
    }

    /**
    * Create Device Value.
    * @param Request $request
    * @return Response
    */
    #[Route('', methods: ['POST'])]
    #[IsGranted('ROLE_DEVICE_VALUE_ADD')]
    #[OA\Tag(name: 'Admin Device Value')]
    #[OA\Response(response: 201, description: 'Returns Created Device Value', content: new OA\JsonContent(properties: [
        new OA\Property(property: "success", type: "boolean"),
        new OA\Property(property: 'data', ref: new Model(type: DeviceValue::class, groups: ['details']))
    ]))]
    #[OA\RequestBody(
        content: [
            'multipart/form-data' => new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(ref: new Model(type: DeviceValueType::class))
            ),
            'application/json' => new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(ref: new Model(type: DeviceValueType::class))
            ),
        ]
    )]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    public function create(Request $request): Response
    {
        $deviceValue = new DeviceValue();

        $form = $this->createForm(DeviceValueType::class, $deviceValue);

        $this->submitAttempt($form, $request->request->all());
        $this->deviceValueService->add($deviceValue);

        $this->rest->succeeded()->setData($deviceValue);

        return $this
            ->setGroup(['details'])
            ->viewResponse($this->rest->getResponse(), Response::HTTP_CREATED);
    }

    /**
    * Update Device Value By id.
    * @param Request $request
    * @param DeviceValue $deviceValue
    * @return Response
    */
    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['PUT'])]
    #[IsGranted('ROLE_DEVICE_VALUE_UPDATE')]
    #[OA\Tag(name: 'Admin Device Value')]
    #[OA\Response(response: 201, description: 'Returns Updated Device Value.', content: new OA\JsonContent(properties: [
        new OA\Property(property: "success", type: "boolean"),
        new OA\Property(property: 'data', ref: new Model(type: DeviceValue::class, groups: ['details']))
    ]))]
    #[OA\RequestBody(
        content: [
            'multipart/form-data' => new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(ref: new Model(type: DeviceValueType::class))
            ),
            'application/json' => new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(ref: new Model(type: DeviceValueType::class))
            ),
        ]
    )]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    public function update(Request $request, DeviceValue $deviceValue): Response
    {
        $form = $this->createForm(DeviceValueType::class, $deviceValue);

        $this->submitAttempt($form, $request->request->all(), false);

        $this->deviceValueService->update($deviceValue);

        $this->rest->succeeded()->setData($deviceValue);

        return $this
            ->setGroup(['details'])
            ->viewResponse($this->rest->getResponse(), Response::HTTP_OK);
    }

    /**
    * Delete Device Value By id.
    * @param DeviceValue $deviceValue
    * @return Response
    */
    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    #[IsGranted('ROLE_DEVICE_VALUE_DELETE')]
    #[OA\Tag(name: 'Admin Device Value')]
    #[OA\Response(
        response: 201,
        description: 'Returns Deleted Device Value.',
        content: new OA\JsonContent(properties: [
            new OA\Property(property: "success", type: "boolean"),
            new OA\Property(property: 'data', ref: new Model(type: DeviceValue::class, groups: ['details']))
        ])
    )]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    public function delete(DeviceValue $deviceValue): Response
    {
        $this->deviceValueService->delete($deviceValue);

        $this->rest->succeeded()->setData($deviceValue);

        return $this
            ->setGroup(['details'])
            ->viewResponse($this->rest->getResponse(), Response::HTTP_OK);
    }
}