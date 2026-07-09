<?php

declare(strict_types=1);

namespace App\Service\Game;

interface EndGameHandlerInterface
{
    public function endGame(string $gameId, string $winnerId): void;
}
