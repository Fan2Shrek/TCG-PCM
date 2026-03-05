<?php

declare(strict_types=1);

namespace App\Game\Card\Effect;

use App\Enum\CardEffectEnum;
use App\Game\AbstractCard;
use App\Game\GameContext;

/** @consistent-constructor */
abstract class AbstractCardEffect
{
    protected function __construct(array $data = []) {}

    abstract public static function getName(): CardEffectEnum;

    public function modifyValue(float|int $value, AbstractCard $card): float|int
    {
        return $value;
    }

    public function beforeAction(AbstractCard $card, GameContext $gameContext): void
    {
        // no-op by default
    }

    public static function fromEffectState(EffectState $effectState): static
    {
        if ($effectState->effect !== static::getName()) {
            throw new \InvalidArgumentException(sprintf('Invalid effect state for %s effect', self::getName()->value));
        }

        return new static($effectState->data);
    }
}
