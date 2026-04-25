<?php

namespace App\Controller\Api\Mobile;

use App\Controller\RestController;
use App\Entity\DeviceValue;
use App\Form\DeviceValueType;
use App\Service\DeviceValueService;
use App\Service\RestHelperService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/esp32/device-values')]
class DeviceValueController extends RestController
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
    * Create Device Value.
    * @param Request $request
    * @return Response
    */
    #[Route('', methods: ['POST'])]
    #[OA\Tag(name: 'ESP32 Device Value')]
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
    #[OA\Tag(name: 'ESP32 Device Value')]
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
}