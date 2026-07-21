<?php

declare(strict_types=1);

namespace App\Game\Card\Interface;

use App\Game\GameContext;
use App\Game\State\GameEvent;

interface TurnAwareInterface
{
    public function onTurnStart(GameEvent $event, GameContext $gameContext): void;

    public function onTurnEnd(GameEvent $event, GameContext $gameContext): void;
}
