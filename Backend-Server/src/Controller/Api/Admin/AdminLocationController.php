<?php

namespace App\Controller\Api\Admin;

use App\Controller\RestController;
use App\Service\LocationAggregationService;
use App\Service\LocationTrackingService;
use App\Service\RestHelperService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/locations')]
class AdminLocationController extends RestController
{
    public function __construct(
        RestHelperService $rest,
        EntityManagerInterface $em,
        private readonly LocationAggregationService $locationAggregationService,
        private readonly LocationTrackingService $locationTrackingService
    ) {
        parent::__construct($rest, $em);
    }

    #[Route('', methods: ['GET'])]
    #[OA\Tag(name: 'Admin Locations')]
    #[OA\Response(
        response: 200,
        description: 'Aggregated map markers (centers, devices, forests, fires, fire brigades, mobile sensors, cars, addresses).',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean'),
                new OA\Property(
                    property: 'data',
                    properties: [
                        new OA\Property(
                            property: 'items',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'source', type: 'string', example: 'CENTER'),
                                    new OA\Property(property: 'id', type: 'integer'),
                                    new OA\Property(property: 'latitude', type: 'string'),
                                    new OA\Property(property: 'longitude', type: 'string'),
                                    new OA\Property(property: 'label', type: 'string'),
                                    new OA\Property(property: 'related', type: 'object'),
                                ],
                                type: 'object'
                            )
                        ),
                    ],
                    type: 'object'
                ),
            ]
        )
    )]
    #[OA\Parameter(ref: '#components/parameters/locale')]
    #[Security(name: 'Bearer')]
    public function index(): Response
    {
        $items = $this->locationAggregationService->getAllMarkers();
        $this->rest->succeeded()->setData(['items' => $items]);

        return $this
            ->clearGroup()
            ->viewResponse($this->rest->getResponse(), Response::HTTP_OK);
    }

    #[Route('/live', methods: ['GET'])]
    #[OA\Tag(name: 'Admin Locations')]
    #[OA\Parameter(
        name: 'sourceType',
        description: 'Filter by source type.',
        in: 'query',
        schema: new OA\Schema(type: 'string', enum: ['CAR', 'MOBILE_SENSOR'], example: 'MOBILE_SENSOR')
    )]
    #[OA\Parameter(
        name: 'sourceId',
        description: 'Filter by source id.',
        in: 'query',
        schema: new OA\Schema(type: 'integer', example: 15)
    )]
    #[OA\Parameter(
        name: 'center',
        description: 'Filter by center id.',
        in: 'query',
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\Parameter(ref: '#components/parameters/locale')]
    #[Security(name: 'Bearer')]
    public function live(Request $request): Response
    {
        $items = $this->locationTrackingService->getLive(
            $request->query->get('sourceType'),
            $request->query->getInt('sourceId') ?: null,
            $request->query->getInt('center') ?: null
        );
        $this->rest->succeeded()->setData(['items' => $items]);

        return $this
            ->clearGroup()
            ->viewResponse($this->rest->getResponse(), Response::HTTP_OK);
    }

    #[Route('/history', methods: ['GET'])]
    #[OA\Tag(name: 'Admin Locations')]
    #[OA\Parameter(
        name: 'sourceType',
        description: 'Filter by source type.',
        in: 'query',
        schema: new OA\Schema(type: 'string', enum: ['CAR', 'MOBILE_SENSOR'], example: 'CAR')
    )]
    #[OA\Parameter(
        name: 'sourceId',
        description: 'Filter by source id.',
        in: 'query',
        schema: new OA\Schema(type: 'integer', example: 15)
    )]
    #[OA\Parameter(
        name: 'center',
        description: 'Filter by center id.',
        in: 'query',
        schema: new OA\Schema(type: 'integer', example: 1)
    )]
    #[OA\Parameter(name: 'day', description: 'Filter one day (YYYY-MM-DD).', in: 'query', schema: new OA\Schema(type: 'string', example: '2026-03-27'))]
    #[OA\Parameter(name: 'month', description: 'Filter one month (YYYY-MM).', in: 'query', schema: new OA\Schema(type: 'string', example: '2026-03'))]
    #[OA\Parameter(name: 'year', description: 'Filter one year (YYYY).', in: 'query', schema: new OA\Schema(type: 'string', example: '2026'))]
    #[OA\Parameter(name: 'from', description: 'Start datetime (ISO).', in: 'query', schema: new OA\Schema(type: 'string', format: 'date-time', example: '2026-03-27T00:00:00+00:00'))]
    #[OA\Parameter(name: 'to', description: 'End datetime (ISO).', in: 'query', schema: new OA\Schema(type: 'string', format: 'date-time', example: '2026-03-27T23:59:59+00:00'))]
    #[OA\Parameter(name: 'limit', description: 'Maximum records.', in: 'query', schema: new OA\Schema(type: 'integer', example: 1000))]
    #[OA\Parameter(ref: '#components/parameters/locale')]
    #[Security(name: 'Bearer')]
    public function history(Request $request): Response
    {
        $items = $this->locationTrackingService->getHistory([
            'sourceType' => $request->query->get('sourceType'),
            'sourceId' => $request->query->getInt('sourceId') ?: null,
            'centerId' => $request->query->getInt('center') ?: null,
            'limit' => $request->query->getInt('limit', 1000),
            'day' => $request->query->get('day'),
            'month' => $request->query->get('month'),
            'year' => $request->query->get('year'),
            'from' => $request->query->get('from'),
            'to' => $request->query->get('to'),
        ]);
        $this->rest->succeeded()->setData(['items' => $items]);

        return $this
            ->clearGroup()
            ->viewResponse($this->rest->getResponse(), Response::HTTP_OK);
    }
}
