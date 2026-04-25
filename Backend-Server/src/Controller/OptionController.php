<?php

namespace App\Controller;

use App\Constant\EmergencyRequestStatus;
use App\Constant\UserGender;
use App\Constant\FireStatusType;
use App\Entity\MobileSensor;
use App\Service\RestHelperService;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/api/options')]
class OptionController extends RestController
{
    private TranslatorInterface $translator;

    public function __construct(
        RestHelperService      $rest,
        EntityManagerInterface $em,
        UserService $userService,
        TranslatorInterface $translator
    ) {
        parent::__construct($rest, $em);
        $this->translator = $translator;
    }

    /**
     * List all option endpoints (canonical enum sources for the API).
     * @OA\Tag(name="Options")
     * @OA\Response(
     *     response=200,
     *     description="Map of domain keys to relative paths under /api/options.",
     *     @OA\JsonContent(
     *             @OA\Property(property="success",type="boolean"),
     *             @OA\Property(property="data",type="object",
     *                  @OA\Property(property="users.gender", type="object"),
     *                  @OA\Property(property="fires.status", type="object"),
     *                  @OA\Property(property="emergencyRequests.status", type="object"),
     *                  @OA\Property(property="mobileSensors.type", type="object")
     *             )
     *      )
     * )
     * @OA\Parameter(ref="#components/parameters/locale")
     * @return Response
     */
    #[Route('', methods: ['GET'])]
    #[Route('/', methods: ['GET'])]
    public function index(): Response
    {
        $base = '/api/options';
        $this->rest->succeeded()->setData([
            'users.gender' => [
                'description' => 'User gender enum (use each item `value` in forms)',
                'method' => 'GET',
                'path' => $base.'/users/gender/types',
            ],
            'fires.status' => [
                'description' => 'Fire status enum',
                'method' => 'GET',
                'path' => $base.'/fires/status/types',
            ],
            'emergencyRequests.status' => [
                'description' => 'Emergency request status enum',
                'method' => 'GET',
                'path' => $base.'/emergency-requests/status',
            ],
            'mobileSensors.type' => [
                'description' => 'Mobile sensor type (DRONE vs CAR_ROBOT)',
                'method' => 'GET',
                'path' => $base.'/mobile-sensors/types',
            ],
        ]);

        return $this
            ->viewResponse(
                $this->rest->getResponse(),
                Response::HTTP_OK
            );
    }

    /**
     * Get Gender Type.
     * @OA\Tag(name="Options")
     * @OA\Response(
     *     response=200,
     *     description="Returns Gender Types.",
     *     @OA\JsonContent(
     *             @OA\Property(property="success",type="boolean"),
     *             @OA\Property(property="data",type="array",
     *                  @OA\Items(
     *                  @OA\Property(property="label",type="string",example="Pending"),
     *                  @OA\Property(property="value",type="string",example="PENDING"),
     *                  )
     *              )
     *      )
     * )
     * @OA\Parameter(ref="#components/parameters/locale")
     * @return Response
     */
    #[Route('/users/gender/types', methods: ["GET"])]
    #[Route('/users/gender/types/', methods: ["GET"])]
    public function getGenderTypes(): Response
    {
        $data = UserGender::getOptions($this->translator);

        $this->rest->succeeded()->setData($data);

        return $this
            ->viewResponse(
                $this->rest->getResponse(),
                Response::HTTP_OK
            );
    }
    
    /**
    * Get Fire Status Type.
    * @OA\Tag(name="Options")
    * @OA\Response(
    *     response=200,
    *     description="Returns Fire Status Types.",
    *     @OA\JsonContent(
    *             @OA\Property(property="success",type="boolean"),
    *             @OA\Property(property="data",type="array",
    *                  @OA\Items(
    *                  @OA\Property(property="label",type="string",example="Pending"),
    *                  @OA\Property(property="value",type="string",example="PENDING"),
    *                  )
    *              )
    *      )
    * )
    * @OA\Parameter(ref="#components/parameters/locale")
    * @return Response
    */
    #[Route('/fires/status/types', methods: ["GET"])]
    #[Route('/fires/status/types/', methods: ["GET"])]
    public function getFireTypes(): Response
    {
        $data = FireStatusType::getOptions($this->translator);

        $this->rest->succeeded()->setData($data);

        return $this
            ->viewResponse(
                $this->rest->getResponse(),
                Response::HTTP_OK
            );
    }
    
    /**
    * Get Emergency Request Status Type.
    * @OA\Tag(name="Options")
    * @OA\Response(
    *     response=200,
    *     description="Returns Emergency Request Status Types.",
    *     @OA\JsonContent(
    *             @OA\Property(property="success",type="boolean"),
    *             @OA\Property(property="data",type="array",
    *                  @OA\Items(
    *                  @OA\Property(property="label",type="string",example="Done"),
    *                  @OA\Property(property="value",type="string",example="DONE"),
    *                  )
    *              )
    *      )
    * )
    * @OA\Parameter(ref="#components/parameters/locale")
    * @return Response
    */
    #[Route('/emergency-requests/status', methods: ["GET"])]
    #[Route('/emergency-requests/status/', methods: ["GET"])]
    public function getEmergencyRequestStatus(): Response
    {
        $data = EmergencyRequestStatus::getOptions($this->translator);

        $this->rest->succeeded()->setData($data);

        return $this
            ->viewResponse(
                $this->rest->getResponse(),
                Response::HTTP_OK
            );
    }

    /**
     * Get mobile sensor type values (DRONE, CAR_ROBOT).
     * @OA\Tag(name="Options")
     * @OA\Response(
     *     response=200,
     *     description="Returns mobile sensor types.",
     *     @OA\JsonContent(
     *             @OA\Property(property="success",type="boolean"),
     *             @OA\Property(property="data",type="array",
     *                  @OA\Items(
     *                  @OA\Property(property="label",type="string",example="Drone"),
     *                  @OA\Property(property="value",type="string",example="DRONE"),
     *                  )
     *              )
     *      )
     * )
     * @OA\Parameter(ref="#components/parameters/locale")
     * @return Response
     */
    #[Route('/mobile-sensors/types', methods: ['GET'])]
    #[Route('/mobile-sensors/types/', methods: ['GET'])]
    public function getMobileSensorTypes(): Response
    {
        $data = [
            ['label' => 'Drone', 'value' => MobileSensor::TYPE_DRONE],
            ['label' => 'Car robot', 'value' => MobileSensor::TYPE_CAR_ROBOT],
        ];

        $this->rest->succeeded()->setData($data);

        return $this
            ->viewResponse(
                $this->rest->getResponse(),
                Response::HTTP_OK
            );
    }
}
