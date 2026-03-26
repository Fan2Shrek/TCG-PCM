<?php

declare(strict_types=1);

namespace App\Service\Game\State;

use App\Game\State\GameState;

interface GameStateRepositoryInterface
{
    public function save(GameState $gameState, string $room): void;

    public function get(string $room): ?GameState;
}
