<?php

declare(strict_types=1);

namespace App\Game\Card\Monster;

use App\Game\AbstractCard;
use App\Game\Card\CardState;
use App\Game\Card\MonsterCardState;
use App\Game\GameContext;

abstract class AbstractMonsterCard extends AbstractCard
{
    protected int $currentHealthPoints;

    abstract public function getAttack(): int;

    abstract public function getHealPoints(): int;

    public function setState(CardState $state): void
    {
        if (!$state instanceof MonsterCardState) {
            throw new \InvalidArgumentException('State must be an instance of MonsterCardState');
        }

        parent::setState($state);

        $this->currentHealthPoints = $state->currentHealthPoints;
    }

    public function onMonsterPlayed(GameContext $context): void
    {
        // no-op
    }
}
