<?php

declare(strict_types=1);

namespace App\Service\Booster\Types;

interface BoosterInterface
{
    public function getCapacity(): int;

    /**
     * @return array<string, mixed>
     */
    public function getCardsCriteria(): array;
}
