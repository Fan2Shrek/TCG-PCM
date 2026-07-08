<?php

declare(strict_types=1);

namespace App\Domain\Command\User;

use App\Service\User\UserRegistrar;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class RegisterHandler
{
    public function __construct(
        private UserRegistrar $userRegistrar,
    ) {}

    public function __invoke(RegisterCommand $command): void
    {
        $this->userRegistrar->register($command->username, $command->password);
    }
}
