<?php

declare(strict_types=1);

namespace App\Service\Game;

use App\Game\State\GameState;

interface EndGameHandlerInterface
{
    public function endGame(string $gameId, GameState $gameState, string $winnerId): void;
}
