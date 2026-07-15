<?php

declare(strict_types=1);

namespace App\Game\Card\Effect;

use App\Enum\CardEffectEnum;
use App\Game\AbstractCard;

final class HackedCardEffect extends AbstractCardEffect
{
    public const int MIN_MODIFIER = 33;
    public const int MAX_MODIFIER = 300;

    private readonly int $value;

    public function __construct(array $data = [])
    {
        if (!($value = $data['value'] ?? null)) {
            throw new \InvalidArgumentException('Missing value key');
        }

        if (!\is_int($value)) {
            throw new \InvalidArgumentException('Value must be an integer');
        }

        if ($value < self::MIN_MODIFIER || $value > self::MAX_MODIFIER) {
            throw new \InvalidArgumentException(sprintf('Value must be between %s and %s', self::MIN_MODIFIER, self::MAX_MODIFIER));
        }

        $this->value = $value;
    }

    public static function getName(): CardEffectEnum
    {
        return CardEffectEnum::HACKED;
    }

    public function modifyValue(float|int $value, AbstractCard $card): float|int
    {
        return $value * ($this->value / 100);
    }
}
