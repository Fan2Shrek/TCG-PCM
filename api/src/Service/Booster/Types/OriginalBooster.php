<?php

declare(strict_types=1);

namespace App\Service\Booster\Types;

use App\Enum\CardSetEnum;
use App\Game\Card\Character\AbstractCharacterCard;
use Symfony\Component\Translation\TranslatableMessage;

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
            'excludeType' => AbstractCharacterCard::class,
        ];
    }
}
