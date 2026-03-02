<?php

declare(strict_types=1);

namespace App\Game\Card\Effect;

use App\Enum\CardEffectEnum;
use App\Game\AbstractCard;

final class HackedCardEffect extends AbstractCardEffect
{
    public const float MIN_MODIFIER = 0.3;
    public const float MAX_MODIFIER = 3;

    public function __construct(
        private readonly float $value,
    ) {}

    public function getName(): CardEffectEnum
    {
        return CardEffectEnum::HACKED;
    }

    public function modifyValue(float|int $value, AbstractCard $card): float|int
    {
        return $value * $this->value;
    }
}
