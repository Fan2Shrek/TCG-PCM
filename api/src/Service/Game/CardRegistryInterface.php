<?php

declare(strict_types=1);

namespace App\Service\Game;

use App\Enum\CardRarityEnum;
use App\Game\AbstractCard;

interface CardRegistryInterface
{
    public function getCardTemplateById(string $cardId): AbstractCard;

    /**
     * @return string[]
     */
    public function getAllByRarity(CardRarityEnum $rarity): array;
}
