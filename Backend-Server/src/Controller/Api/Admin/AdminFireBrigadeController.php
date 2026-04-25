<?php

namespace App\Controller\Api\Admin;

use App\Controller\RestController;
use App\Entity\FireBrigade;
use App\Form\FireBrigadeType;
use App\Request\FireBrigadeRequest;
use App\Service\FireBrigadeService;
use App\Service\RestHelperService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/fire-brigades')]
class AdminFireBrigadeController extends RestController
{
    public function __construct(
        RestHelperService $rest,
        EntityManagerInterface $em,
        private readonly FireBrigadeService $fireBrigadeService
    )
    {
        parent::__construct($rest, $em);
    }

    /**
     * Returns Get Fire Brigades.
     * @param FireBrigadeRequest $fireBrigadeRequest
     * @return Response
     */
    #[Route('', methods: ['GET'])]
    #[IsGranted('ROLE_FIRE_BRIGADE_LIST')]
    #[OA\Tag(name: 'Admin Fire Brigade')]
    #[OA\Response(response: 200, description: 'Returns Fires', content: new OA\JsonContent(properties: array(
        new OA\Property(property: "success", type: "boolean"),
        new OA\Property(property: "pagination", properties: array(
            new OA\Property(property: 'page', type: 'integer', example: 1),
            new OA\Property(property: 'pages', type: 'integer', example: 1),
            new OA\Property(property: 'totalItems', type: 'integer', example: 100),
            new OA\Property(property: "items", type: "array",
                items: new OA\Items(
                    ref: new Model(type: FireBrigade::class, groups: array("list"))
                )
            ),
        )),
    )))]
    #[OA\Parameter(ref: "#components/parameters/direction")]
    #[OA\Parameter(ref: "#components/parameters/page")]
    #[OA\Parameter(ref: "#components/parameters/limit")]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    #[OA\Parameter(name: "sort", description: "Sorting results by specified attribute.", in: "query",
        schema: new OA\Schema(type: "string", enum: ["fb.id","fb.name","fb.createdAt"]),
        example: "fb.id"
    )]
    #[OA\Parameter(name: "center", description: "Apply filter by forest.", in: "query",
        schema: new OA\Schema(type: "integer", example: 1),
    )]
    #[Security(name: "Bearer")]
    public function index(FireBrigadeRequest $fireBrigadeRequest): Response
    {
        $page = $fireBrigadeRequest->getPage();
        $limit = $fireBrigadeRequest->getLimit();
        $filters = $fireBrigadeRequest->getFilters();
        $fireBrigadeRequest->validateSort();

        $pagination = $this->fireBrigadeService->getList($page, $limit, $filters);
        $this->rest->setPagination($pagination);

        return $this
            ->setGroup(["list"])
            ->viewResponse($this->rest->getResponse(),
                Response::HTTP_OK);
    }

    /**
     * Get Fire Brigade By id.
     * @param FireBrigade $fireBrigade
     * @return Response
     */
    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted('ROLE_FIRE_BRIGADE_SHOW')]
    #[OA\Tag(name: 'Admin Fire Brigade')]
    #[OA\Response(response: 200, description: 'Returns Fire Brigade By id', content: new OA\JsonContent(properties: [
        new OA\Property(property: "success", type: "boolean"),
        new OA\Property(property: "data", ref: new Model(type: FireBrigade::class, groups: ['details']))
    ]))]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    public function show(FireBrigade $fireBrigade): Response
    {
        $this->rest->succeeded()->setData($fireBrigade);

        return $this
            ->setGroup(['details'])
            ->viewResponse($this->rest->getResponse(), Response::HTTP_OK);
    }

    /**
     * Create Fire Brigade.
     * @param Request $request
     * @return Response
     */
    #[Route('', methods: ['POST'])]
    #[IsGranted('ROLE_FIRE_BRIGADE_ADD')]
    #[OA\Tag(name: 'Admin Fire Brigade')]
    #[OA\Response(response: 201, description: 'Returns Created Fire', content: new OA\JsonContent(properties: [
        new OA\Property(property: "success", type: "boolean"),
        new OA\Property(property: 'data', ref: new Model(type: FireBrigade::class, groups: ['details']))
    ]))]
    #[OA\RequestBody(
        content: [
            'multipart/form-data' => new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(ref: new Model(type: FireBrigadeType::class))
            ),
            'application/json' => new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(ref: new Model(type: FireBrigadeType::class))
            ),
        ]
    )]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    public function create(Request $request): Response
    {
        $fireBrigade = new FireBrigade();

        $form = $this->createForm(FireBrigadeType::class, $fireBrigade);

        $this->submitAttempt($form, $request->request->all());
        $password = $form?->get('password')?->getData();
        $this->fireBrigadeService->add($fireBrigade, $password);

        $this->rest->succeeded()->setData($fireBrigade);

        return $this
            ->setGroup(['details'])
            ->viewResponse($this->rest->getResponse(), Response::HTTP_CREATED);
    }

    /**
     * Update Fire Brigade By id.
     * @param Request $request
     * @param FireBrigade $fireBrigade
     * @return Response
     */
    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['PUT'])]
    #[IsGranted('ROLE_FIRE_BRIGADE_UPDATE')]
    #[OA\Tag(name: 'Admin Fire Brigade')]
    #[OA\Response(response: 201, description: 'Returns Updated Fire Brigade.', content: new OA\JsonContent(properties: [
        new OA\Property(property: "success", type: "boolean"),
        new OA\Property(property: 'data', ref: new Model(type: FireBrigade::class, groups: ['details']))
    ]))]
    #[OA\RequestBody(
        content: [
            'multipart/form-data' => new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(ref: new Model(type: FireBrigadeType::class))
            ),
            'application/json' => new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(ref: new Model(type: FireBrigadeType::class))
            ),
        ]
    )]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    public function update(Request $request, FireBrigade $fireBrigade): Response
    {
        $form = $this->createForm(FireBrigadeType::class, $fireBrigade);

        $this->submitAttempt($form, $request->request->all(), false);

        $this->fireBrigadeService->update($fireBrigade);

        $this->rest->succeeded()->setData($fireBrigade);

        return $this
            ->setGroup(['details'])
            ->viewResponse($this->rest->getResponse(), Response::HTTP_OK);
    }

    /**
     * Delete Fire Brigade By id.
     * @param FireBrigade $fireBrigade
     * @return Response
     */
    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    #[IsGranted('ROLE_FIRE_BRIGADE_DELETE')]
    #[OA\Tag(name: 'Admin Fire Brigade')]
    #[OA\Response(
        response: 201,
        description: 'Returns Deleted Fire Brigade.',
        content: new OA\JsonContent(properties: [
            new OA\Property(property: "success", type: "boolean"),
            new OA\Property(property: 'data', ref: new Model(type: FireBrigade::class, groups: ['details']))
        ])
    )]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    public function delete(FireBrigade $fireBrigade): Response
    {
        $this->fireBrigadeService->delete($fireBrigade);

        $this->rest->succeeded()->setData($fireBrigade);

        return $this
            ->setGroup(['details'])
            ->viewResponse($this->rest->getResponse(), Response::HTTP_OK);
    }
}