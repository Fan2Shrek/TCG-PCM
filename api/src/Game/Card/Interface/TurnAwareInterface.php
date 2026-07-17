<?php

declare(strict_types=1);

namespace App\Game\Card\Interface;

use App\Game\GameContext;

interface TurnAwareInterface
{
    public function onTurnStart(GameContext $gameContext): void;

    /**
     * @note This method is called at the end of the turn, after the event has been applied
     */
    public function onTurnEnd(GameContext $gameContext): void;
}
