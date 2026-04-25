<?php

namespace App\Controller\Api\Admin;

use App\Controller\RestController;
use App\Service\CollectionSystemService;
use App\Service\RestHelperService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/collection-systems')]

class AdminCollectionSystemController extends RestController
{
    public function __construct(
        RestHelperService $rest,
        EntityManagerInterface $em,
        private readonly CollectionSystemService $collectionSystemService
    )
    
    {
        parent::__construct($rest, $em);
    }

    /**
     * Returns Collection System Centers and Fires and Forests.
     * @return Response
     */
    #[Route('', methods: ['GET'])]
    #[OA\Tag(name: 'Admin Collection System')]
    #[OA\Parameter(ref: "#components/parameters/locale")]
    #[Security(name: "Bearer")]
    public function index(): Response
    {
        $data = $this->collectionSystemService->getCollectionSystem();
        
        $this->rest->succeeded()->setData($data);

        return $this
            ->setGroup(["list", 'details'])
            ->viewResponse($this->rest->getResponse(),
                Response::HTTP_OK);
    }
}