<?php

namespace App\Controller\Api\Admin;

use App\Constant\EmergencyRequestStatus;
use App\Controller\RestController;
use App\Entity\EmergencyRequest;
use App\Form\EmergencyRequestType;
use App\Request\EmergencyRequestRequest;
use App\Service\EmergencyRequestService;
use App\Service\RestHelperService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/emergency-requests')]
class AdminEmergencyRequestController extends RestController
{
    public function __construct(
        RestHelperService $rest,
        EntityManagerInterface $em,
        private readonly EmergencyRequestService $emergencyRequestService
    )
    {
        parent::__construct($rest, $em);
    }

    /**
    * Returns Get Emergency Requests.
    * @param EmergencyRequestRequest $emergencyRequestRequest
    * @return Response
    */
    #[Route('', methods: ['GET'])]
    #[IsGranted('ROLE_EMERGENCY_REQUEST_LIST')]
    #[OA\Tag(name: 'Admin SOS Emergency Request')]
    #[OA\Response(response: 200, description: 'Returns Emergency Request', content: new OA\JsonContent(properties: array(
        new OA\Property(property: "success", type: "boolean"),
        new OA\Property(property: "pagination", properties: array(
            new OA\Property(property: 'page', type: 'integer', example: 1),
            new OA\Property(property: 'pages', type: 'integer', example: 1),
            new OA\Property(property: 'totalItems', type: 'integer', example: 100),
            new OA\Property(property: "items", type: "array",
                items: new OA\Items(
                    ref: new Model(type: EmergencyRequest::class, groups: array("list"))
                )
            ),
        )),
    )))]
    #[OA\Parameter(ref: "#components/parameters/direction")]
    #[OA\Parameter(ref: "#components/parameters/page")]
    #[OA\Parameter(ref: "#components/parameters/limit")]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    #[OA\Parameter(name: "sort", description: "Sorting results by specified attribute.", in: "query",
        schema: new OA\Schema(type: "string", enum: ["em.id","em.status","em.createdAt"]),
        example: "em.id"
    )]
    #[OA\Parameter(name: "center", description: "Apply filter by center.", in: "query",
        schema: new OA\Schema(type: "integer", example: 1),
    )]
    #[OA\Parameter(name: "status", description: "Apply filter by status. Allowed `value` values from GET /api/options/emergency-requests/status.", in: "query",
        schema: new OA\Schema(type: "string", enum: EmergencyRequestStatus::VALUES, example: "DANGEROUS"),
    )]
    #[OA\Parameter(name: "fire", description: "Apply filter by fire.", in: "query",
        schema: new OA\Schema(type: "integer", example: 1),
    )]
    #[OA\Parameter(name: "fireBrigade", description: "Apply filter by fire brigade.", in: "query",
        schema: new OA\Schema(type: "integer", example: 1),
    )]
    #[Security(name: "Bearer")]
    public function index(EmergencyRequestRequest $emergencyRequestRequest): Response
    {
        $page = $emergencyRequestRequest->getPage();
        $limit = $emergencyRequestRequest->getLimit();
        $filters = $emergencyRequestRequest->getFilters();
        $emergencyRequestRequest->validateSort();

        $pagination = $this->emergencyRequestService->getList($page, $limit, $filters);
        $this->rest->setPagination($pagination);

        return $this
            ->setGroup(["list"])
            ->viewResponse($this->rest->getResponse(),
                Response::HTTP_OK);
    }

    /**
     * Get Emergency Request By id.
     * @param EmergencyRequest $emergencyRequest
     * @return Response
     */
    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted('ROLE_EMERGENCY_REQUEST_SHOW')]
    #[OA\Tag(name: 'Admin SOS Emergency Request')]
    #[OA\Response(response: 200, description: 'Returns Emergency Request By id', content: new OA\JsonContent(properties: [
        new OA\Property(property: "success", type: "boolean"),
        new OA\Property(property: "data", ref: new Model(type: EmergencyRequest::class, groups: ['details']))
    ]))]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    public function show(EmergencyRequest $emergencyRequest): Response
    {
        $this->rest->succeeded()->setData($emergencyRequest);

        return $this
            ->setGroup(['details'])
            ->viewResponse($this->rest->getResponse(), Response::HTTP_OK);
    }


    /**
     * Update Emergency Request By id.
     * @param Request $request
     * @param EmergencyRequest $emergencyRequest
     * @return Response
     */
    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['PUT'])]
    #[IsGranted('ROLE_EMERGENCY_REQUEST_UPDATE')]
    #[OA\Tag(name: 'Admin SOS Emergency Request')]
    #[OA\Response(response: 201, description: 'Returns Updated Emergency Request.', content: new OA\JsonContent(properties: [
        new OA\Property(property: "success", type: "boolean"),
        new OA\Property(property: 'data', ref: new Model(type: EmergencyRequest::class, groups: ['details']))
    ]))]
    #[OA\RequestBody(
        content: [
            'multipart/form-data' => new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(ref: new Model(type: EmergencyRequestType::class))
            ),
            'application/json' => new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(ref: new Model(type: EmergencyRequestType::class))
            ),
        ]
    )]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    public function update(Request $request, EmergencyRequest $emergencyRequest): Response
    {
        $form = $this->createForm(EmergencyRequestType::class, $emergencyRequest);

        $this->submitAttempt($form, $request->request->all(), false);

        $this->emergencyRequestService->update($emergencyRequest);

        $this->rest->succeeded()->setData($emergencyRequest);

        return $this
            ->setGroup(['details'])
            ->viewResponse($this->rest->getResponse(), Response::HTTP_OK);
    }

}