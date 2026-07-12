<?php

declare(strict_types=1);

namespace App\Service\Booster\Types;

use App\Enum\CardSetEnum;
use App\Game\Card\Character\AbstractCharacterCard;

final class IsaacBooster implements BoosterInterface
{
    public function getCapacity(): int
    {
        return 5;
    }

    public function getCardsCriteria(): array
    {
        return [
            'serie' => CardSetEnum::TBOI,
            'excludeType' => AbstractCharacterCard::class,
        ];
    }
}
