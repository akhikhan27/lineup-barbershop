<?php

namespace App\Service;

use Psr\Http\Message\UploadedFileInterface;

class UploadService
{
    private string $uploadDir;

    public function __construct(string $uploadDir)
    {
        $this->uploadDir = rtrim($uploadDir, '/');
    }

    public function upload(UploadedFileInterface $file, ?string $oldFilename = null): ?string
    {
        if ($file->getError() !== UPLOAD_ERR_OK) {
            return null;
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file->getClientMediaType(), $allowedTypes)) {
            return null;
        }

        if ($file->getSize() > 5 * 1024 * 1024) {
            return null;
        }

        $ext = pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $file->moveTo($this->uploadDir . '/' . $filename);

        if ($oldFilename && $oldFilename !== 'placeholder.jpg') {
            $oldPath = $this->uploadDir . '/' . $oldFilename;
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        return $filename;
    }
}
