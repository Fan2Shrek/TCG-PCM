<?php

declare(strict_types=1);

namespace App\Api\DTO;

use App\Enum\CardRarityEnum;
use App\Enum\CardSetEnum;
use App\Enum\CardTypeEnum;

final readonly class CollectionCardDTO
{
    // @mago-ignore lint:excessive-parameter-list
    public function __construct(
        public string $name,
        public string $description,
        public string $image,
        public CardRarityEnum $rarity,
        public CardSetEnum $set,
        public string $instanceId,
        public ?CardTypeEnum $type = null,
        public ?int $cost = null,
        public ?int $hp = null,
        public ?int $attack = null,
        public bool $isNewToCollection = false,
    ) {}
}
