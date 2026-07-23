<?php

declare(strict_types=1);

namespace App\Api\DTO;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Api\Provider\GameDataProvider;
use App\Domain\Model\CardEffect;

#[ApiResource(operations: [
    new Get(uriTemplate: '/game-data', status: 200, provider: GameDataProvider::class),
])]
final readonly class GameDataDTO
{
    /**
     * @param array<string, CardEffect> $cardEffects
     * @param array<string, CardDTO> $cards
     */
    public function __construct(
        public array $cardEffects,
        public array $cards,
    ) {}
}
