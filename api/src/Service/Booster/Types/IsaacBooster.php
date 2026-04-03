<?php

declare(strict_types=1);

namespace App\Service\Booster\Types;

use App\Enum\CardSetEnum;

final class IsaacBooster implements BoosterInterface
{
    public function getCapacity(): int
    {
        return 5;
    }

    public function getCardsCriteria(): array
    {
        return [
            'series' => CardSetEnum::TBOI,
        ];
    }
}
