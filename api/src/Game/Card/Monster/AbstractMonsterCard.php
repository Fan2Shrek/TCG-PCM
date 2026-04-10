<?php

declare(strict_types=1);

namespace App\Game\Card\Monster;

use App\Enum\CardEffectEnum;
use App\Game\AbstractCard;
use App\Game\Card\CardState;
use App\Game\Card\Effect\PowerBoostEffect;
use App\Game\Card\MonsterCardState;
use App\Game\GameContext;

abstract class AbstractMonsterCard extends AbstractCard
{
    protected int $currentHealthPoints;

    protected bool $canAttack = true;

    public function getAttack(): int
    {
        $value = $this->getValue($this->getBaseAttack(), true);

        /** @var PowerBoostEffect|null $boost */
        if ($boost = $this->getEffect(CardEffectEnum::POWER_BOOST)) {
            $value *= $boost->getValue();
        }

        return (int) $value;
    }

    abstract public function getBaseAttack(): int;

    abstract public function getHealPoints(): int;

    public function setState(CardState $state): void
    {
        parent::setState($state);

        if (!$state instanceof MonsterCardState) {
            return;
        }

        $this->currentHealthPoints = $state->currentHealthPoints;
        $this->canAttack = $state->canAttack;
    }

    public function onMonsterPlayed(GameContext $context): void
    {
        // no-op
    }

    public function onMonsterDeath(GameContext $context): void
    {
        // no-op
    }

    public function canAttack(): bool
    {
        return $this->canAttack;
    }

    public function getCurrentHealthPoints(): int
    {
        return $this->currentHealthPoints;
    }
}
