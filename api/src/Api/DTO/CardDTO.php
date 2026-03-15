<?php

declare(strict_types=1);

namespace App\Api\DTO;

use App\Enum\CardRarityEnum;
use App\Enum\CardSetEnum;
use App\Game\Card\Effect\EffectState;

final readonly class CardDTO
{
    /**
     * @param EffectState[] $effects
     */
    public function __construct(
        public string $name,
        public string $description,
        public string $image,
        public CardRarityEnum $rarity,
        public CardSetEnum $set,
        public string $instanceId,
        public array $effects,
    ) {}
}
