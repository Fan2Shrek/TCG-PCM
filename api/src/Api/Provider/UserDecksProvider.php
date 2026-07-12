<?php

declare(strict_types=1);

namespace App\Api\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Deck;
use App\Repository\DeckRepository;
use App\Service\Auth\CurrentUserProviderInterface;

/**
 * @implements ProviderInterface<Deck>
 */
final class UserDecksProvider implements ProviderInterface
{
    public function __construct(
        private CurrentUserProviderInterface $currentUserProvider,
        private DeckRepository $deckRepository,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        return $this->deckRepository->findActiveByUser($this->currentUserProvider->getCurrentUser());
    }
}
