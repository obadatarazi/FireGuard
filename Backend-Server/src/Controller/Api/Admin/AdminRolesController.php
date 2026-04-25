<?php

namespace App\Controller\Api\Admin;

use App\Controller\RestController;
use App\Service\RestHelperService;
use App\Service\RolesService;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/roles')]
class AdminRolesController extends RestController
{
    private RolesService $rolesService;

    public function __construct(
        EntityManagerInterface $em,
        RestHelperService $rest,
        RolesService $rolesService,
    ) {
        parent::__construct($rest, $em);
        $this->rolesService = $rolesService;
    }

    /**
     *Return Roles.
     * @OA\Tag(name="Admin Roles")
     * @OA\Response(
     *     response=200,
     *     description="Returns Roles",
     *     @OA\JsonContent(
     *          @OA\Property(property="success",type="boolean"),
     *          @OA\Property(property="data",type="array",@OA\Items()))
     *
     *     )
     * )
     * @OA\Parameter(ref="#components/parameters/locale")
     * @return Response
     */
    #[Route('', methods: ['GET'])]
    #[IsGranted('ROLE_SYSTEM_ROLE_LIST')]
    public function list(): Response
    {
        $rolesGroups = $this->rolesService->getAll();

        $this->rest->succeeded()->setData($rolesGroups);

        return $this->viewResponse($this->rest->getResponse(), Response::HTTP_OK);
    }

    /**
     *Return Roles By Section.
     * @OA\Tag(name="Admin Roles")
     * @OA\Response(
     *     response=200,
     *     description="Returns Roles by section",
     *     @OA\JsonContent(
     *          @OA\Property(property="success",type="boolean"),
     *          @OA\Property(property="data",type="array",
     *              @OA\Items(
     *                  @OA\Property(type="object",
     *                      @OA\Property(property="section",type="string",example="SUPER_ADMIN"),
     *                      @OA\Property(property="roles",type="object",
     *                          @OA\Property(property="role",type="string",example="SUPER_ADMIN"),
     *                          @OA\Property(property="name",type="string",example="SUPER_ADMIN"),
     *                          @OA\Property(property="description",type="string",example="SUPER_ADMIN")
     *                      ),
     *                  )
     *              )
     *          )
     *     )
     * )
     * @OA\Parameter(ref="#components/parameters/locale")
     * @return Response
     */
    #[Route('/by-section', methods: ['GET'])]
    #[IsGranted('ROLE_SYSTEM_ROLE_LIST')]
    public function getBySection(): Response
    {
        $rolesGroups = $this->rolesService->getBySection();

        $this->rest->succeeded()->setData($rolesGroups);

        return $this->viewResponse($this->rest->getResponse(), Response::HTTP_OK);
    }
}
