<?php

declare(strict_types=1);

namespace App\Domain\Command\User;

final class RequestPasswordResetCommand
{
    public function __construct(
        public readonly string $email,
    ) {}
}
