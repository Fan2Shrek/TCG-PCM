<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelInterface;

final class UserProfilePictureUpdater
{
    private const array ALLOWED_MIME_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    private const int MAX_FILE_SIZE = 5 * 1024 * 1024;

    private const string UPLOAD_DIR = 'uploads/avatars';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private KernelInterface $kernel,
    ) {}

    public function update(User $user, ?UploadedFile $file): string
    {
        if (null === $file) {
            throw HttpException::fromStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY, 'No file uploaded.');
        }

        if (!$file->isValid()) {
            throw HttpException::fromStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY, 'Invalid file upload.');
        }

        $size = $file->getSize();
        if (false === $size || $size > self::MAX_FILE_SIZE) {
            throw HttpException::fromStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY, 'File is too large (max 5MB).');
        }

        $mimeType = (string) mime_content_type($file->getPathname());
        $extension = self::ALLOWED_MIME_TYPES[$mimeType] ?? null;
        if (null === $extension) {
            throw HttpException::fromStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY, 'Unsupported image type. Allowed: JPEG, PNG, WEBP.');
        }

        $uploadDir = $this->kernel->getProjectDir().'/public/'.self::UPLOAD_DIR;
        $filename = (string) $user->getId().'-'.uniqid().'.'.$extension;

        $file->move($uploadDir, $filename);

        $previousPath = $user->getProfilePicturePath();
        if (null !== $previousPath) {
            $previousFile = $this->kernel->getProjectDir().'/public/'.$previousPath;
            if (is_file($previousFile)) {
                unlink($previousFile);
            }
        }

        $newPath = self::UPLOAD_DIR.'/'.$filename;
        $user->setProfilePicturePath($newPath);

        $this->entityManager->flush();

        return $newPath;
    }
}
