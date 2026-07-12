<?php

declare(strict_types=1);

namespace App\Domain\Command\User;

final class ResetPasswordCommand
{
    public function __construct(
        #[\SensitiveParameter]
        public readonly string $token,
        #[\SensitiveParameter]
        public readonly string $newPassword,
    ) {}
}
