<?php

declare(strict_types=1);

namespace App\Service\Booster\Types;

use App\Enum\CardSetEnum;

final class BigBooster implements BoosterInterface
{
    public function getCapacity(): int
    {
        return 7;
    }

    public function getCardsCriteria(): array
    {
        return [
            CardSetEnum::ORIGINAL,
        ];
    }
}
