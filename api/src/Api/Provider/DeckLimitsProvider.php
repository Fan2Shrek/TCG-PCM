<?php

declare(strict_types=1);

namespace App\Api\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Domain\Model\DeckLimits;
use App\Service\DeckValidator;

/**
 * @implements ProviderInterface<DeckLimits>
 */
final class DeckLimitsProvider implements ProviderInterface
{
    public function __construct(
        private DeckValidator $deckValidator,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): DeckLimits
    {
        return new DeckLimits(
            deckSize: $this->deckValidator->getDeckSize(),
            maxCardCopies: $this->deckValidator->getMaxCardCopies(),
            rarityLimits: $this->deckValidator->getRarityLimitsForApi(),
        );
    }
}
