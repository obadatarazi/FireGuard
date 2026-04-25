<?php

namespace App\Controller\Api\Admin;

use App\Controller\RestController;
use App\Service\DemoImportService;
use App\Service\RestHelperService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/admin/demo')]
class DemoImportController extends RestController
{
    public function __construct(
        RestHelperService $rest,
        EntityManagerInterface $em,
        private readonly DemoImportService $demoImportService,
        private readonly KernelInterface $kernel,
    ) {
        parent::__construct($rest, $em);
    }

    /**
     * Import demo data from bundled XML or an uploaded file (append-only). Requires admin JWT and ROLE_USER_LIST.
     */
    #[Route('/import', methods: ['POST'])]
    #[IsGranted('ROLE_USER_LIST')]
    #[OA\Tag(name: 'Admin Demo')]
    #[OA\RequestBody(
        content: [
            'multipart/form-data' => new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(
                            property: 'file',
                            description: 'Optional XML file; if omitted, data/demo_seed.xml is used.',
                            type: 'string',
                            format: 'binary'
                        ),
                    ]
                )
            ),
            'application/json' => new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(
                            property: 'useBundled',
                            description: 'If true, ignore file and use bundled demo_seed.xml',
                            type: 'boolean',
                            example: true
                        ),
                    ]
                )
            ),
        ]
    )]
    #[OA\Response(
        response: 200,
        description: 'Import result',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean'),
                new OA\Property(property: 'inserted', type: 'object'),
                new OA\Property(property: 'message', type: 'string', nullable: true),
            ]
        )
    )]
    #[OA\Response(response: 403, description: 'Forbidden (e.g. invalid or missing Bearer token)')]
    #[OA\Response(response: 422, description: 'Invalid XML or validation error')]
    #[OA\Parameter(ref: '#components/parameters/locale')]
    #[Security(name: 'Bearer')]
    public function import(Request $request): Response
    {
        $xml = null;

        /** @var UploadedFile|null $upload */
        $upload = $request->files->get('file');
        if ($upload instanceof UploadedFile && $upload->isValid()) {
            $xml = @file_get_contents($upload->getPathname());
        }

        if ($xml === null || $xml === false || $xml === '') {
            $path = $this->kernel->getProjectDir().'/data/demo_seed.xml';
            if (!is_readable($path)) {
                return $this->json(
                    ['success' => false, 'message' => 'No file uploaded and bundled demo_seed.xml is missing at '.$path],
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }
            $xml = file_get_contents($path);
        }

        $result = $this->demoImportService->importFromXml($xml);

        $status = $result['success'] ? Response::HTTP_OK : Response::HTTP_UNPROCESSABLE_ENTITY;

        return $this->json($result, $status);
    }
}
