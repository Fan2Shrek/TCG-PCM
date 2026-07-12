<?php

declare(strict_types=1);

namespace App\Game\Card\Effect;

use App\Enum\CardEffectEnum;

final class PowerBoostEffect extends AbstractCardEffect
{
    private readonly float $value;

    public function __construct(array $data = [])
    {
        if (!($value = $data['value'] ?? null)) {
            throw new \InvalidArgumentException('Missing value key');
        }

        if (!\is_int($value) && !\is_float($value)) {
            throw new \InvalidArgumentException('Value must be int or float');
        }

        $this->value = (float) $value;
    }

    public static function getName(): CardEffectEnum
    {
        return CardEffectEnum::POWER_BOOST;
    }

    public function getValue(): float
    {
        return $this->value;
    }
}
