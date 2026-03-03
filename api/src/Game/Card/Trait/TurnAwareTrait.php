<?php

declare(strict_types=1);

namespace App\Game\Card\Trait;

use App\Game\GameContext;

trait TurnAwareTrait
{
    public function onTurnStart(GameContext $gameContext): void
    {
        // Default implementation does nothing
    }

    public function onTurnEnd(GameContext $gameContext): void
    {
        // Default implementation does nothing
    }
}
