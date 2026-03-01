<?php

declare(strict_types=1);

namespace App\Service\Game;

use App\Game\State\GameEvent;
use App\Game\State\GameState;

interface GameEventApplierInterface
{
    public function apply(GameEvent $event, GameState $gameState): GameState;

    /**
     * @param GameEvent[] $events
     */
    public function applyMultiple(array $events, GameState $gameState): GameState;
}
