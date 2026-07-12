<?php

declare(strict_types=1);

namespace App\Domain\Command\User;

use Symfony\Component\HttpFoundation\File\UploadedFile;

final class SetProfilePictureCommand
{
    public function __construct(
        public readonly ?UploadedFile $profilePicture = null,
    ) {}
}
