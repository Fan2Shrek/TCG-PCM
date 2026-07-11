<?php

declare(strict_types=1);

namespace App\Service\Booster\Types;
use Symfony\Component\Translation\TranslatableMessage;
use App\Enum\CardSetEnum;

final class BtdBooster implements BoosterInterface
{
    public function getCapacity(): int
    {
        return 5;
    }

    public function getCardsCriteria(): array
    {
        return [
            'serie' => CardSetEnum::BTD6,
        ];
    }
}
