<?php

declare(strict_types=1);

namespace App\Service\Booster\Types;

use App\Game\Card\Character\AbstractCharacterCard;

final class CharacterBooster implements BoosterInterface
{
    public function getCapacity(): int
    {
        return 1;
    }

    public function getCardsCriteria(): array
    {
        return [
            'type' => AbstractCharacterCard::class,
        ];
    }
}
