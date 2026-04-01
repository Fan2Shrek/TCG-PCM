<?php

namespace App\Api\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\User;
use App\Service\Auth\CurrentUserProviderInterface;

/**
 * @implements ProviderInterface<User>
 */
class UserProvider implements ProviderInterface
{
    public function __construct(
        private CurrentUserProviderInterface $currentUserProvider,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): User
    {
        return $this->currentUserProvider->getCurrentUser();
    }
}
