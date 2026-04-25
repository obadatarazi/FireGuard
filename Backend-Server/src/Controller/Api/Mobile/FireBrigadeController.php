<?php

namespace App\Controller\Api\Mobile;

use App\Entity\FireBrigade;
use App\Controller\RestController;
use App\Service\FireBrigadeService;
use App\Service\RestHelperService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/mobile/fire-brigades')]
class FireBrigadeController extends RestController
{
    public function __construct(
        RestHelperService $rest,
        EntityManagerInterface $em,
    )
    {
        parent::__construct($rest, $em);
    }

    /**
     * Get Profile Data Mobile.
     * @OA\Response(
     *     response=200,
     *     description="Returns the Fire Brigade Profile",
     *     @OA\JsonContent(
     *             @OA\Property(property="success",type="boolean"),
     *             @OA\Property(property="data",ref=@Model(type=FireBrigade::class,groups={"details"}))
     *      )
     * )
     * @OA\Parameter(ref="#components/parameters/locale")
     * @OA\Tag(name="Auth Mobile")
     * @Security(name="Bearer")
     * @return Response
     */
    #[Route('/profile', methods: ['GET'])]
    public function getProfileData(): Response
    {
        $user = $this->getUser();

        $this->rest->succeeded()->setData(['fireBrigades' => $user]);

        return $this
            ->setGroup(['details'])
            ->viewResponse($this->rest->getResponse(),
                Response::HTTP_OK);
    }
}