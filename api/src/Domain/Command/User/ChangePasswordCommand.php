<?php

declare(strict_types=1);

namespace App\Domain\Command\User;

final class ChangePasswordCommand
{
    public function __construct(
        #[\SensitiveParameter]
        public readonly string $currentPassword,
        #[\SensitiveParameter]
        public readonly string $newPassword,
    ) {}
}
