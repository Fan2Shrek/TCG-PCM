<?php

declare(strict_types=1);

namespace App\Domain\Model;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Api\Provider\DeckLimitsProvider;

#[ApiResource(operations: [
    new Get(uriTemplate: '/decks/limits', provider: DeckLimitsProvider::class, status: 200),
])]
final readonly class DeckLimits
{
    /**
     * @param array<string, int> $rarityLimits
     */
    public function __construct(
        public int $deckSize,
        public int $maxCardCopies,
        public array $rarityLimits,
    ) {}
}
