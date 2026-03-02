<?php

declare(strict_types=1);

namespace App\Game\Card\Effect;

use App\Enum\CardEffectEnum;
use App\Game\AbstractCard;

abstract class AbstractCardEffect
{
    abstract public function getName(): CardEffectEnum;

    public function modifyValue(float|int $value, AbstractCard $card): float|int
    {
        return $value;
    }
}
