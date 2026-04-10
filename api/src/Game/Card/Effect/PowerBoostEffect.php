<?php

declare(strict_types=1);

namespace App\Game\Card\Effect;

use App\Enum\CardEffectEnum;

final class PowerBoostEffect extends AbstractCardEffect
{
    private readonly int $value;

    public function __construct(array $data = [])
    {
        if (!($value = $data['value'] ?? null)) {
            throw new \InvalidArgumentException('Missing value key');
        }

        if (!\is_int($value)) {
            throw new \InvalidArgumentException('Value must be int');
        }

        $this->value = $value;
    }

    public static function getName(): CardEffectEnum
    {
        return CardEffectEnum::POWER_BOOST;
    }

    public function getValue(): int
    {
        return $this->value;
    }
}
