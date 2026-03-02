<?php

declare(strict_types=1);

namespace App\Game\Card\Effect;

use App\Enum\CardEffectEnum;
use App\Game\AbstractCard;

/** @consistent-constructor */
abstract class AbstractCardEffect
{
    protected function __construct(array $data = []) {}

    abstract public static function getName(): CardEffectEnum;

    public function modifyValue(float|int $value, AbstractCard $card): float|int
    {
        return $value;
    }

    public static function fromEffectState(EffectState $effectState): static
    {
        if ($effectState->effect !== self::getName()) {
            throw new \InvalidArgumentException(sprintf('Invalid effect state for %s effect', self::getName()->value));
        }

        return new static($effectState->data);
    }
}
