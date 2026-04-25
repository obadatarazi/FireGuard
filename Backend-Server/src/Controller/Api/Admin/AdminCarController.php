<?php

namespace App\Controller\Api\Admin;

use App\Controller\RestController;
use App\Entity\Car;
use App\Form\CarType;
use App\Request\CarRequest;
use App\Service\CarService;
use App\Service\RestHelperService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/cars')]
class AdminCarController extends RestController
{
    public function __construct(
        RestHelperService $rest,
        EntityManagerInterface $em,
        private readonly CarService $carService
    ) {
        parent::__construct($rest, $em);
    }

    /**
     * Returns Get Cars.
     * @param CarRequest $carRequest
     * @return Response
     */
    #[Route('', methods: ['GET'])]
    #[IsGranted('ROLE_CAR_LIST')]
    #[OA\Tag(name: 'Admin Car')]
    #[OA\Response(response: 200, description: 'Returns Cars', content: new OA\JsonContent(
        properties: array(
            new OA\Property(property: "success", type: "boolean"),
            new OA\Property(
                property: "pagination",
                properties: array(
                    new OA\Property(property: 'page', type: 'integer', example: 1),
                    new OA\Property(property: 'pages', type: 'integer', example: 1),
                    new OA\Property(property: 'totalItems', type: 'integer', example: 100),
                    new OA\Property(
                        property: "items",
                        type: "array",
                        items: new OA\Items(
                            ref: new Model(type: Car::class, groups: array("list"))
                        )
                    ),
                )
            ),
        )
    )
    )]
    #[OA\Parameter(ref: "#components/parameters/direction")]
    #[OA\Parameter(ref: "#components/parameters/page")]
    #[OA\Parameter(ref: "#components/parameters/limit")]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    #[OA\Parameter(name: "sort", description: "Sorting results by specified attribute.", in: "query",
        schema: new OA\Schema(type: "string", enum: ["ca.id", "ca.name", "ca.createdAt"]),
        example: "ca.id"
    )]
    #[OA\Parameter(name: "search", description: "Apply filter by name.", in: "query",
        schema: new OA\Schema(type: "string", example: "KIA"),
    )]
    #[OA\Parameter(name: "numberPlate", description: "Apply filter by number plate.", in: "query",
        schema: new OA\Schema(type: "string", example: "6456456"),
    )]
    #[OA\Parameter(name: "model", description: "Apply filter by model.", in: "query",
        schema: new OA\Schema(type: "string", example: "RIO"),
    )]
    #[OA\Parameter(name: "center", description: "Apply filter by center.", in: "query",
        schema: new OA\Schema(type: "integer", example: 1),
    )]
    #[Security(name: "Bearer")]
    public function index(CarRequest $carRequest): Response
    {
        $page = $carRequest->getPage();
        $limit = $carRequest->getLimit();
        $filters = $carRequest->getFilters();
        $carRequest->validateSort();

        $pagination = $this->carService->getList($page, $limit, $filters);
        $this->rest->setPagination($pagination);

        return $this
            ->setGroup(["list"])
            ->viewResponse(
                $this->rest->getResponse(),
                Response::HTTP_OK
            );
    }

    /**
     * Get Car By id.
     * @param Car $car
     * @return Response
     */
    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted('ROLE_CAR_SHOW')]
    #[OA\Tag(name: 'Admin Car')]
    #[OA\Response(response: 200, description: 'Returns Car By id', content: new OA\JsonContent(properties: [
        new OA\Property(property: "success", type: "boolean"),
        new OA\Property(property: "data", ref: new Model(type: Car::class, groups: ['details']))
    ]))]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    public function show(Car $car): Response
    {
        $this->rest->succeeded()->setData($car);

        return $this
            ->setGroup(['details'])
            ->viewResponse($this->rest->getResponse(), Response::HTTP_OK);
    }

    /**
     * Create Car.
     * @param Request $request
     * @return Response
     */
    #[Route('', methods: ['POST'])]
    #[IsGranted('ROLE_CAR_ADD')]
    #[OA\Tag(name: 'Admin Car')]
    #[OA\Response(response: 201, description: 'Returns Created Car', content: new OA\JsonContent(properties: [
        new OA\Property(property: "success", type: "boolean"),
        new OA\Property(property: 'data', ref: new Model(type: Car::class, groups: ['details']))
    ]))]
    #[OA\RequestBody(
        content: [
            'multipart/form-data' => new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    ref: new Model(type: CarType::class)
                )
            ),
            'application/json' => new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    ref: new Model(type: CarType::class)
                )
            ),
        ]
    )]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    public function create(Request $request): Response
    {
        $car = new Car();

        $form = $this->createForm(CarType::class, $car);

        $this->submitAttempt($form, $request->request->all());

        $this->carService->add($car);

        $this->rest->succeeded()->setData($car);

        return $this
            ->setGroup(['details'])
            ->viewResponse($this->rest->getResponse(), Response::HTTP_CREATED);
    }

    /**
     * Update Car By id.
     * @param Request $request
     * @param Car $car
     * @return Response
     */
    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['PUT'])]
    #[IsGranted('ROLE_CAR_UPDATE')]
    #[OA\Tag(name: 'Admin Car')]
    #[OA\Response(response: 201, description: 'Returns Updated Car.', content: new OA\JsonContent(properties: [
        new OA\Property(property: "success", type: "boolean"),
        new OA\Property(property: 'data', ref: new Model(type: Car::class, groups: ['details']))
    ]))]
    #[OA\RequestBody(
        content: [
            'multipart/form-data' => new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    ref: new Model(type: CarType::class)
                )
            ),
            'application/json' => new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    ref: new Model(type: CarType::class)
                )
            ),
        ]
    )]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    public function update(Request $request, Car $car): Response
    {
        $form = $this->createForm(CarType::class, $car);

        $this->submitAttempt($form, $request->request->all(), false);

        $this->carService->update($car);

        $this->rest->succeeded()->setData($car);

        return $this
            ->setGroup(['details'])
            ->viewResponse($this->rest->getResponse(), Response::HTTP_OK);
    }

    /**
     * Delete Car By id.
     * @param Car $car
     * @return Response
     */
    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    #[IsGranted('ROLE_CAR_DELETE')]
    #[OA\Tag(name: 'Admin Car')]
    #[OA\Response(
        response: 201,
        description: 'Returns Deleted Car.',
        content: new OA\JsonContent(properties: [
            new OA\Property(property: "success", type: "boolean"),
            new OA\Property(property: 'data', ref: new Model(type: Car::class, groups: ['details']))
        ])
    )]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    public function delete(Car $car): Response
    {
        $this->carService->delete($car);

        $this->rest->succeeded()->setData($car);

        return $this
            ->setGroup(['details'])
            ->viewResponse($this->rest->getResponse(), Response::HTTP_OK);
    }
}