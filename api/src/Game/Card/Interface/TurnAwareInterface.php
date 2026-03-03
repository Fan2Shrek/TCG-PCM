<?php

declare(strict_types=1);

namespace App\Game\Card\Interface;

use App\Game\GameContext;

interface TurnAwareInterface
{
    public function onTurnStart(GameContext $gameContext): void;

    public function onTurnEnd(GameContext $gameContext): void;
}
