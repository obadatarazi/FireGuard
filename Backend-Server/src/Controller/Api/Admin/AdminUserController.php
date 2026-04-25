<?php

namespace App\Controller\Api\Admin;

use App\Controller\RestController;
use App\Entity\User;
use App\Exception\FormValidationHttpException;
use App\Form\User\ChangeMyPasswordType;
use App\Form\User\UpdateMyProfileType;
use App\Form\User\UserType;
use App\Request\UserRequest;
use App\Service\RestHelperService;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/api/admin/users')]
class AdminUserController extends RestController
{
    private UserService $userService;

    public function __construct(
        EntityManagerInterface $em,
        RestHelperService $rest,
        UserService $userService,
    ) {
        parent::__construct($rest, $em);
        $this->userService = $userService;
    }

    /**
     * Lists all Users.
     * @OA\Response(
     *     response=200,
     *     description="Returns the Users",
     *     @OA\JsonContent(
     *             @OA\Property(property="success",type="boolean"),
     *             @OA\Property(property="pagination",type="object",
     *                  @OA\Property(property="page",type="integer",example=1),
     *                  @OA\Property(property="pages",type="integer",example=10),
     *                  @OA\Property(property="totalItems",type="integer",example=100),
     *                  @OA\Property(property="items",type="array",
     *                      @OA\Items(ref=@Model(type=User::class,groups={"list"}))
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
     *     example="u.id",
     *     description="Sorting results by specified attribute.",
     *     @OA\Schema(type="string",enum={"u.id","u.fullNameAr","u.fullNameEn","u.createdAt"})
     * )
     * @OA\Parameter(
     *     name="search",
     *     in="query",
     *     example="sami murad",
     *     description="Apply filter by email, full_name, phone_number.",
     *     @OA\Schema(type="string")
     * )
     * @OA\Parameter(
     *     name="phoneNumber",
     *     in="query",
     *     example="099256478494",
     *     description="Apply filter by phone number",
     *     @OA\Schema(type="string")
     * )
     * @OA\Parameter(
     *     name="rolesGroup",
     *     in="query",
     *     example=1,
     *     description="Apply filter by rolesGroup.",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Parameter(
     *     name="gender",
     *     in="query",
     *     example="MALE",
     *     description="Apply filter by gender.",
     *     @OA\Schema(type="string",enum={"MALE","FEMALE"})
     * )
     * @Security(name="Bearer")
     * @OA\Tag(name="Admin User")
     */
    #[Route('', methods: ['GET'])]
    #[IsGranted('ROLE_USER_LIST')]
    public function index(UserRequest $userRequest): Response
    {
        $page = $userRequest->getPage();
        $limit = $userRequest->getLimit();
        $filters = $userRequest->getFilters();
        $userRequest->validateSort();

        $pagination = $this->userService->getList($page, $limit, $filters);
        $this->rest->setPagination($pagination);

        return $this
            ->setGroup(["list"])
            ->viewResponse($this->rest->getResponse(),
                Response::HTTP_OK);
    }

    /**
     * Create User.
     * @OA\RequestBody(
     *      description="Add new User",
     *      @OA\MediaType(
     *          mediaType="multipart/form-data",
     *          @OA\Schema(ref="#/components/schemas/UserForm")
     *      )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Returns Created User",
     *     @OA\JsonContent(
     *             @OA\Property(property="success",type="boolean"),
     *             @OA\Property(property="data",ref=@Model(type=User::class,groups={"details"}))
     *      )
     * )
     * @OA\Tag(name="Admin User")
     * @OA\Parameter(ref="#components/parameters/locale")
     * @param Request $request
     * @return Response
     */
    #[Route('', methods: ['POST'])]
    #[IsGranted('ROLE_USER_ADD')]
    public function create(Request $request): Response
    {
        $user = new User();

        $form = $this->createForm(UserType::class, $user, ["edit" => false]);
        $this->submitAttempt($form, array_merge($request->files->all(), $request->request->all()));
        $files = $request->files;
        $password = $form?->get('password')?->getData();

        $this->userService->add($user, $form, $files, $password);

        $this->rest->setData($user);

        return $this
            ->setGroup(["details"])
            ->viewResponse($this->rest->getResponse(), Response::HTTP_CREATED);
    }

    /**
     * Update User.
     * @OA\RequestBody(
     *      description="Edit User",
     *      @OA\MediaType(
     *          mediaType="multipart/form-data",
     *          @OA\Schema(ref="#/components/schemas/UpdateUserForm")
     *      )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Returns Updated User",
     *     @OA\JsonContent(
     *             @OA\Property(property="success",type="boolean"),
     *             @OA\Property(property="data",ref=@Model(type=User::class,groups={"details"}))
     *      )
     * )
     * @OA\Tag(name="Admin User")
     * @OA\Parameter(ref="#components/parameters/locale")
     * @param Request $request
     * @param User $user
     * @return Response
     */
    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_USER_UPDATE')]
    public function update(Request $request, User $user): Response
    {
        $form = $this->createForm(UserType::class, $user, ["edit" => true]);

        $this->submitAttempt($form, array_merge($request->files->all(), $request->request->all()), false);

        $hasAvatar = $request->files->has("avatar")
            || ($request->request->has("avatar"));

        $this->userService->update($user, $form, $hasAvatar);

        $this->rest->setData($user);

        return $this
            ->setGroup(['details'])
            ->viewResponse($this->rest->getResponse(), Response::HTTP_OK);
    }

    /**
     * Delete User.
     * @OA\Tag(name="Admin User")
     * @OA\Parameter(ref="#components/parameters/locale")
     * @OA\Response(
     *     response=200,
     *     description="Returns Deleted User",
     *     @OA\JsonContent(
     *             @OA\Property(property="success",type="boolean"),
     *             @OA\Property(property="data",ref=@Model(type=User::class,groups={"details"}))
     *      )
     * )
     * @param User $user
     * @return Response
     */
    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    #[IsGranted('ROLE_USER_DELETE')]
    public function delete(User $user): Response
    {
        $me = $this->getUser();
        if ($me->getId() === $user->getId()) {
            throw new BadRequestHttpException('cannot_deleted_your_account');
        }
        $this->userService->delete($user);
        $this->rest->setData($user);

        return $this
            ->setGroup(["details"])
            ->viewResponse($this->rest->getResponse(), Response::HTTP_OK);
    }

    /**
     * Get User.
     * @OA\Response(
     *     response=200,
     *     description="Returns the User",
     *     @OA\JsonContent(
     *             @OA\Property(property="success",type="boolean"),
     *             @OA\Property(property="data",ref=@Model(type=User::class,groups={"details"}))
     *      )
     * )
     * @OA\Parameter(ref="#components/parameters/locale")
     * @OA\Tag(name="Admin User")
     * @param User $user
     * @return Response
     */
    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted('ROLE_USER_SHOW')]
    public function show(User $user): Response
    {
        $this->rest->succeeded()->setData($user);

        return $this
            ->setGroup(["details"])
            ->viewResponse($this->rest->getResponse(),
                Response::HTTP_OK);
    }

    /**
     * Get Profile Data.
     * @OA\Response(
     *     response=200,
     *     description="Returns the User Profile",
     *     @OA\JsonContent(
     *             @OA\Property(property="success",type="boolean"),
     *             @OA\Property(property="data",ref=@Model(type=User::class,groups={"profile", "details"}))
     *      )
     * )
     * @OA\Parameter(ref="#components/parameters/locale")
     * @OA\Tag(name="Admin User")
     * @return Response
     */
    #[Route('/profile', methods: ['GET'])]
    public function getProfileData(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $this->rest->succeeded()->setData(['user' => $user]);

        return $this
            ->setGroup(["profile", 'details'])
            ->viewResponse($this->rest->getResponse(),
                Response::HTTP_OK);
    }

    /**
     * Change my password.
     * @OA\RequestBody(
     *      description="Edit User",
     *      @OA\MediaType(
     *          mediaType="multipart/form-data",
     *          @OA\Schema(
     *              ref=@Model(type=ChangeMyPasswordType::class)
     *          )
     *      ),
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(
     *              ref=@Model(type=ChangeMyPasswordType::class)
     *          )
     *      )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Returns the User Profile",
     *     @OA\JsonContent(
     *             @OA\Property(property="success",type="boolean"),
     *             @OA\Property(property="data",type="object",
     *                  @OA\Property(property="token",type="string")
     *             )
     *      )
     * )
     * @OA\Parameter(ref="#components/parameters/locale")
     * @OA\Tag(name="Admin User")
     * @param TranslatorInterface $translator
     * @param Request $request
     * @param JWTTokenManagerInterface $JWTManager
     * @return Response
     */
    #[Route('/profile/change-password', methods: ['PUT'])]
    public function changeMyPassword(
        TranslatorInterface      $translator,
        Request                  $request,
        JWTTokenManagerInterface $JWTManager
    ): Response
    {
        /**
         * @var User $me
         */
        $me = $this->getUser();
        $form = $this->createForm(ChangeMyPasswordType::class);

        $this->submitAttempt($form, $request->request->all());

        $newPassword = $form->get('newPassword')->getData();
        $confirmPassword = $form->get('confirmPassword')->getData();

        $isMatched = $this->userService
            ->isMatchedPassword(
                $newPassword,
                $confirmPassword);

        if (!$isMatched) {
            $form->get('newPassword')->addError(new FormError($translator->trans('match_password')));
            throw  new FormValidationHttpException($form->getErrors());
        }

        $currentPassword = $form->get('currentPassword')->getData();
        $passChanged = $this->userService->changeUserPassword(
            $me,
            $currentPassword
        );

        if (!$passChanged) {
            $form->get('currentPassword')->addError(new FormError($translator->trans('incorrect_password')));
            throw  new FormValidationHttpException($form->getErrors());
        }

        $this->rest->setData(['token' => $JWTManager->create($me)]);

        return $this
            ->viewResponse($this->rest->getResponse(),
                Response::HTTP_OK);
    }


    /**
     * Update my profile.
     * @OA\RequestBody(
     *      description="Edit User",
     *      @OA\MediaType(
     *          mediaType="multipart/form-data",
     *          @OA\Schema(ref="#/components/schemas/UpdateProfileForm")
     *      ),
     *      @OA\MediaType(
     *          mediaType="application/json",
     *          @OA\Schema(ref="#/components/schemas/UpdateProfileForm")
     *      )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Returns Updated Profile",
     *     @OA\JsonContent(
     *             @OA\Property(property="success",type="boolean"),
     *             @OA\Property(property="data",ref=@Model(type=User::class,groups={"profile"}))
     *      )
     * )
     * @OA\Parameter(ref="#components/parameters/locale")
     * @OA\Tag(name="Admin User")
     * @param Request $request
     * @return Response
     */
    #[Route('/me/edit', methods: ['POST'])]
    public function updateMyProfile(Request $request): Response
    {
        /**
         * @var USer $me
         */
        $me = $this->getUser();
        $form = $this->createForm(UpdateMyProfileType::class, $me);

        $this->submitAttempt($form, array_merge($request->files->all(), $request->request->all()), false);

        $hasAvatar = $request->files->has("avatar")
            || ($request->request->has("avatar"));

        $this->userService->update($me, $form, $hasAvatar);

        $this->rest->succeeded()->setData($me);

        return $this
            ->setGroup(["profile"])
            ->viewResponse($this->rest->getResponse(),
                Response::HTTP_OK);
    }

    /**
     * Return My Roles .
     * @OA\Tag(name="Admin User")
     * @OA\Response(
     *     response=200,
     *     description="Returns My Roles",
     *     @OA\JsonContent(
     *          @OA\Property(property="success",type="boolean"),
     *          @OA\Property(property="data",type="array",
     *              @OA\Items(example="ROLE_USER")
     *          )
     *     )
     * )
     * @OA\Parameter(ref="#components/parameters/locale")
     * @return Response
     */
    #[Route('/me/roles-groups', methods: ['GET'])]
    public function myRoles(): Response
    {
        $me = $this->getUser();

        $this->rest->succeeded()->setData($me->getCustomRolesGroups());

        return $this->viewResponse($this->rest->getResponse(), Response::HTTP_OK);
    }
}
