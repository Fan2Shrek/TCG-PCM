<?php

declare(strict_types=1);

namespace App\Service\Booster\Types;

use App\Enum\CardSetEnum;
use App\Game\Card\Character\AbstractCharacterCard;

final class BigBooster implements BoosterInterface
{
    public function getCapacity(): int
    {
        return 7;
    }

    public function getCardsCriteria(): array
    {
        return [
            'serie' => CardSetEnum::ORIGINAL,
            'excludeType' => AbstractCharacterCard::class,
        ];
    }
}
