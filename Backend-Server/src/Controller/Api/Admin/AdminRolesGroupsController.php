<?php

namespace App\Controller\Api\Admin;

use App\Controller\RestController;
use App\Entity\RolesGroup;
use App\Form\RolesGroup\RolesGroupType;
use App\Request\RolesGroupRequest;
use App\ServiceException\EntityCannotBeDeleted;
use App\ServiceException\EntityCannotBeUpdated;
use App\Service\RestHelperService;
use App\Service\RolesGroupService;
use App\Service\RolesService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

 #[Route('/api/admin/roles-groups')]
class AdminRolesGroupsController extends RestController
{
    private RolesGroupService $rolesGroupService;

    public function __construct(
        EntityManagerInterface $em,
        RestHelperService $rest,
        RolesGroupService $rolesGroupService
    ) {
        parent::__construct($rest, $em);
        $this->rolesGroupService = $rolesGroupService;
    }

    /**
     * Return Roles Group.
     * @OA\Tag(name="Admin Roles Groups")
     * @OA\Response(
     *     response=200,
     *     description="Returns Roles Group",
     *     @OA\JsonContent(
     *             @OA\Property(property="success",type="boolean"),
     *             @OA\Property(property="pagination",type="object",
     *                  @OA\Property(property="page",type="integer",example=1),
     *                  @OA\Property(property="pages",type="integer",example=10),
     *                  @OA\Property(property="totalItems",type="integer",example=100),
     *                  @OA\Property(property="items",type="array",
     *                      @OA\Items(ref=@Model(type=RolesGroup::class,groups={"list"}))
     *                  )
     *              )
     *      )
     * )
     * @OA\Parameter(ref="#components/parameters/locale")
     * @OA\Parameter(ref="#components/parameters/page")
     * @OA\Parameter(ref="#components/parameters/limit")
     * @OA\Parameter(ref="#components/parameters/direction")
     * @OA\Parameter(
     *     name="sort",
     *     in="query",
     *     example="rg.id",
     *     description="Sorting results by specified attribute.",
     *     @OA\Schema(type="string",enum={"rg.id","rg.name","prg.createdAt"})
     * )
     * @OA\Parameter(
     *     name="standard",
     *     in="query",
     *     example=true,
     *     description="Apply filter by standard.",
     *     @OA\Schema(type="boolean")
     * )
     * @OA\Parameter(
     *     name="search",
     *     in="query",
     *     example="Super Admin",
     *     description="Apply filter by name.",
     *     @OA\Schema(type="string")
     * )
     * @param RolesGroupRequest $rolesGroupRequest
     * @return Response
     */
    #[Route('', methods: ['GET'])]
    #[IsGranted('ROLE_SYSTEM_ROLE_GROUP_LIST')]
    public function index(RolesGroupRequest $rolesGroupRequest): Response
    {
        $page = $rolesGroupRequest->getPage();
        $limit = $rolesGroupRequest->getLimit();
        $filters = $rolesGroupRequest->getFilters();
        $rolesGroupRequest->validateSort();

        $rolesGroups = $this->rolesGroupService->getList($page, $limit, $filters);

        $this->rest->succeeded()->setPagination($rolesGroups);

        return $this
            ->setGroup(['list'])
            ->viewResponse($this->rest->getResponse(), Response::HTTP_OK);
    }

    /**
     * Return Roles Group By Id.
     * @OA\Tag(name="Admin Roles Groups")
     * @OA\Response(
     *     response=200,
     *     description="Returns Roles Group",
     *     @OA\JsonContent(
     *          @OA\Property(property="success",type="boolean"),
     *          @OA\Property(property="data",type="object",ref=@Model(type=RolesGroup::class,groups={"details"}))
     *     )
     * )
     * @OA\Parameter(ref="#components/parameters/locale")
     * @param RolesGroup $rolesGroup
     * @return Response
     */
    #[Route('/{id}', methods: ['GET'])]
    #[IsGranted('ROLE_SYSTEM_ROLE_GROUP_SHOW')]
    function show(RolesGroup $rolesGroup): Response
    {
        $this->rest->succeeded()->setData($rolesGroup);

        return $this->setGroup(['details'])->viewResponse($this->rest->getResponse(), Response::HTTP_OK);
    }

    /**
     * Return Created Roles Group.
     * @OA\Tag(name="Admin Roles Groups")
     * @OA\Response(
     *     response=200,
     *     description="Returns Created Roles Group",
     *     @OA\JsonContent(
     *          @OA\Property(property="success",type="boolean"),
     *          @OA\Property(property="data",type="object",ref=@Model(type=RolesGroup::class,groups={"details"}))
     *     )
     * )
     * @OA\RequestBody(
     *     description="Create Roles Groupr Form",
     *     @OA\MediaType(
     *          mediaType="multipart/form-data",
     *          @OA\Schema(ref=@Model(type=RolesGroupType::class))
     *     ),
     *     @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(ref=@Model(type=RolesGroupType::class))
     *     )
     * )
     * @OA\Parameter(ref="#components/parameters/locale")
     * @param Request $request
     * @return Response
     */
    #[Route('', methods: ['POST'])]
    #[IsGranted('ROLE_SYSTEM_ROLE_GROUP_ADD')]
    function create(Request $request): Response
    {
        $rolesGroup = new RolesGroup();

        $form = $this->createForm(RolesGroupType::class, $rolesGroup);

        $this->submitAttempt($form, $request->request->all());

        $rolesGroup->setRoles($form->get('roles')->getData());

        $this->rolesGroupService->add($rolesGroup);

        $this->rest->succeeded()->setData($rolesGroup);

        return $this->setGroup(['details'])->viewResponse($this->rest->getResponse(), Response::HTTP_CREATED);
    }

    /**
     * Return Update Roles Group.
     * @OA\Tag(name="Admin Roles Groups")
     * @OA\Response(
     *     response=200,
     *     description="Returns My Roles",
     *     @OA\JsonContent(
     *          @OA\Property(property="success",type="boolean"),
     *          @OA\Property(property="data",type="object",ref=@Model(type=RolesGroup::class,groups={"details"}))
     *     )
     * )
     * @OA\RequestBody(
     *     description="Create Roles Groupr Form",
     *     @OA\MediaType(
     *          mediaType="multipart/form-data",
     *          @OA\Schema(ref=@Model(type=RolesGroupType::class))
     *     ),
     *     @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(ref=@Model(type=RolesGroupType::class))
     *     )
     * )
     * @OA\Parameter(ref="#components/parameters/locale")
     * @param RolesGroup $rolesGroup
     * @param Request $request
     * @return Response
     * @throws EntityCannotBeUpdated
     */
    #[Route('/{id}', methods: ['PUT'])]
    #[IsGranted('ROLE_SYSTEM_ROLE_GROUP_UPDATE')]
    function update(RolesGroup $rolesGroup, Request $request): Response
    {
        try {
            $form = $this->createForm(RolesGroupType::class, $rolesGroup);

            $this->submitAttempt($form, $request->request->all());

            $rolesGroup->setRoles($form->get('roles')->getData());

            $this->rolesGroupService->update($rolesGroup);

            $this->rest->succeeded()->setData($rolesGroup);

            return $this->setGroup(['details'])->viewResponse($this->rest->getResponse(), Response::HTTP_OK);
        } catch (\exception $ex) {
            if ($ex instanceof EntityCannotBeUpdated) {
                throw new BadRequestHttpException($ex->getMessage());
            }

            throw $ex;
        }

    }

     /**
      * Delete Roles Group .
      * @OA\Tag(name="Admin Roles Groups")
      * @OA\Response(
      *     response=200,
      *     description="Returns My Roles",
      *     @OA\JsonContent(
      *          @OA\Property(property="success",type="boolean"),
      *          @OA\Property(property="data",type="object",ref=@Model(type=RolesGroup::class,groups={"details"}))
      *     )
      * )
      * @OA\Parameter(ref="#components/parameters/locale")
      * @param RolesGroup $rolesGroup
      * @return Response
      * @throws EntityCannotBeDeleted
      */
    #[Route('/{id}', methods: ['DELETE'])]
    #[IsGranted('ROLE_SYSTEM_ROLE_GROUP_DELETE')]
    function delete(RolesGroup $rolesGroup): Response
    {
        try {
            $this->rolesGroupService->delete($rolesGroup);

            $this->rest->succeeded()->setData($rolesGroup);

            return $this->setGroup(['details'])->viewResponse($this->rest->getResponse(), Response::HTTP_OK);
        } catch (\exception $ex) {
            if ($ex instanceof EntityCannotBeDeleted) {
                throw new BadRequestHttpException($ex->getMessage());
            }

            throw $ex;
        }
    }
}
