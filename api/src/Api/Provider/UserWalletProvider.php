<?php

namespace App\Api\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\UserWallet;
use App\Service\Auth\CurrentUserProviderInterface;

/**
 * @implements ProviderInterface<UserWallet>
 */
class UserWalletProvider implements ProviderInterface
{
    public function __construct(
        private CurrentUserProviderInterface $currentUserProvider,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): UserWallet
    {
        $user = $this->currentUserProvider->getCurrentUser();
        
        return $user->getWallet();
    }
}