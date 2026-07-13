<?php

declare(strict_types=1);

namespace App\Service\Booster\Types;

use App\Enum\CardSetEnum;



final class OriginalBooster implements BoosterInterface
{
    public function getCapacity(): int
    {
        return 5;
    }

    public function getCardsCriteria(): array
    {
        return [
            'serie' => CardSetEnum::ORIGINAL,
            /* 'excludeType' => AbstractCharacterCard::class, */
        ];
    }
}
