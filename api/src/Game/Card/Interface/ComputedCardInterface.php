<?php

declare(strict_types=1);

namespace App\Game\Card\Interface;

interface ComputedCardInterface
{
    public function computeValue(): mixed;

    public function setComputedValue(mixed $value): void;
}
