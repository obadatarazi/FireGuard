<?php

namespace App\Service;

use App\Kernel;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Uid\Uuid;

class UploadFileService
{
    private Kernel $kernel;

    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
    }

    public function getUploadRelativePath($entityPath): string
    {
        $dateTime = new \DateTime();

        return "/uploads/{$entityPath}/{$dateTime->format('M-Y')}/";
    }

    public function getAbstractPath($filePath): string
    {
        return "{$this->kernel->getProjectDir()}/public/{$filePath}";
    }

    public function checkOrCreateFolder($folderPath): void
    {
        $fs = new Filesystem();
        if (!$fs->exists($folderPath)) {
            $fs->mkdir($folderPath, 0755);
        }
    }

    public function uploadFile(UploadedFile $uploadedFile, $entityPath): array
    {
        $fileName = Uuid::v4();
        $extension = strtolower($uploadedFile->getClientOriginalExtension());
        $fileSize = $uploadedFile->getSize();
        $relativePath = $this->getUploadRelativePath($entityPath);
        $abstractPath = $this->getAbstractPath($relativePath);
        if ($extension) {
            $fileName = "{$fileName}.{$extension}";
        }
        $this->checkOrCreateFolder($abstractPath);

        $uploadedFile->move($abstractPath, $fileName);

        return [
            "fileName" => $fileName,
            "fileSize" => $fileSize,
            "fileUrl" => $relativePath . $fileName
        ];

    }

    public function removeFile($filePath): void
    {
        if ($filePath != null && $filePath != '') {
            $filePath = "{$this->getAbstractPath($filePath)}";
            $fs = new Filesystem();
            if ($fs->exists($filePath)) {
                $fs->remove($filePath);
            }
        }
    }

}