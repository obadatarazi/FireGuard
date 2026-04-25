<?php

namespace App\Controller\Api\DeviceIngest;

use App\Controller\RestController;
use App\Service\LocationTrackingService;
use App\Service\RestHelperService;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/device-ingest/mobile-sensors')]
class DeviceLocationIngestController extends RestController
{
    private const DEVICE_KEY = 'CHANGE_ME_DEVICE_KEY';

    public function __construct(
        RestHelperService $rest,
        EntityManagerInterface $em,
        private readonly LocationTrackingService $locationTrackingService
    ) {
        parent::__construct($rest, $em);
    }

    #[Route('/{id}/location', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[OA\Tag(name: 'Device Ingest Location')]
    #[OA\Response(
        response: 201,
        description: 'Store live location and append history record for one mobile sensor.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(
                    property: 'data',
                    properties: [
                        new OA\Property(property: 'sourceType', type: 'string', enum: ['MOBILE_SENSOR'], example: 'MOBILE_SENSOR'),
                        new OA\Property(property: 'sourceId', type: 'integer', example: 15),
                        new OA\Property(property: 'centerId', type: 'integer', example: 1),
                        new OA\Property(property: 'latitude', type: 'string', example: '32.12345'),
                        new OA\Property(property: 'longitude', type: 'string', example: '35.12345'),
                        new OA\Property(property: 'recordedAt', type: 'string', format: 'date-time', example: '2026-03-27T12:10:00+00:00'),
                    ],
                    type: 'object'
                ),
            ]
        )
    )]
    #[OA\Parameter(
        name: 'X-Device-Key',
        in: 'header',
        required: true,
        description: 'Shared static key for all device clients.',
        schema: new OA\Schema(type: 'string', example: 'CHANGE_ME_DEVICE_KEY')
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: 'Mobile sensor id.',
        schema: new OA\Schema(type: 'integer', example: 15)
    )]
    #[OA\RequestBody(
        required: true,
        content: [
            'application/json' => new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    required: ['latitude', 'longitude'],
                    properties: [
                        new OA\Property(property: 'latitude', type: 'string', example: '32.12345'),
                        new OA\Property(property: 'longitude', type: 'string', example: '35.12345'),
                        new OA\Property(property: 'recordedAt', type: 'string', format: 'date-time', nullable: true, example: '2026-03-27T12:10:00+00:00'),
                    ],
                    type: 'object'
                )
            ),
        ]
    )]
    #[OA\Parameter(ref: '#components/parameters/locale')]
    public function create(Request $request, int $id): Response
    {
        $this->assertDeviceKey($request);
        $payload = $this->getPayload($request);

        $latitude = (string) ($payload['latitude'] ?? '');
        $longitude = (string) ($payload['longitude'] ?? '');
        $recordedAtRaw = $payload['recordedAt'] ?? null;

        if (!$latitude || !$longitude) {
            throw new BadRequestHttpException('latitude and longitude are required.');
        }

        $recordedAt = $recordedAtRaw ? new \DateTime((string) $recordedAtRaw) : null;
        $result = $this->locationTrackingService->record('MOBILE_SENSOR', $id, $latitude, $longitude, $recordedAt);

        $this->rest->succeeded()->setData($result);

        return $this
            ->clearGroup()
            ->viewResponse($this->rest->getResponse(), Response::HTTP_CREATED);
    }

    private function assertDeviceKey(Request $request): void
    {
        $provided = trim((string) $request->headers->get('X-Device-Key', ''));
        if ($provided === '') {
            throw new UnauthorizedHttpException('DeviceKey', 'Missing X-Device-Key header.');
        }

        if (!hash_equals(self::DEVICE_KEY, $provided)) {
            throw new UnauthorizedHttpException('DeviceKey', 'Invalid device key.');
        }
    }

    private function getPayload(Request $request): array
    {
        $content = $request->getContent();
        if (!$content) {
            return $request->request->all();
        }

        try {
            $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
            return is_array($decoded) ? $decoded : [];
        } catch (\JsonException) {
            throw new BadRequestHttpException('Invalid JSON body.');
        }
    }
}
