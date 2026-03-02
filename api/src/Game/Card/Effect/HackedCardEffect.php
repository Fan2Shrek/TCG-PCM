<?php

declare(strict_types=1);

namespace App\Game\Card\Effect;

use App\Enum\CardEffectEnum;
use App\Game\AbstractCard;

final class HackedCardEffect extends AbstractCardEffect
{
    public const float MIN_MODIFIER = 0.3;
    public const float MAX_MODIFIER = 3;

    private readonly float $value;

    public function __construct(array $data = [])
    {
        if (!($value = $data['value'] ?? null)) {
            throw new \InvalidArgumentException('Missing value key');
        }

        if (!\is_float($value)) {
            throw new \InvalidArgumentException('Value must be float');
        }

        $this->value = $value;
    }

    public static function getName(): CardEffectEnum
    {
        return CardEffectEnum::HACKED;
    }

    public function modifyValue(float|int $value, AbstractCard $card): float|int
    {
        return $value * $this->value;
    }
}
