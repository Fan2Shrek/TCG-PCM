<?php

declare(strict_types=1);

namespace App\Service\Booster\Types;

final class DefaultBooster implements BoosterInterface
{
    public function getCapacity(): int
    {
        return 5;
    }

    public function getCardsCriteria(): array
    {
        return [];
    }
}
