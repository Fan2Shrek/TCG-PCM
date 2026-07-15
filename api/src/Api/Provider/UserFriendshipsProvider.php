<?php

declare(strict_types=1);

namespace App\Api\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Friendship;
use App\Repository\FriendshipRepository;
use App\Service\Auth\CurrentUserProviderInterface;

/**
 * @implements ProviderInterface<Friendship>
 */
final class UserFriendshipsProvider implements ProviderInterface
{
    public function __construct(
        private FriendshipRepository $friendshipRepository,
        private CurrentUserProviderInterface $currentUserProvider,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        return $this->friendshipRepository->findAcceptedForUser($this->currentUserProvider->getCurrentUser());
    }
}
