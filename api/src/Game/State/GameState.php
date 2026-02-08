<?php

declare(strict_types=1);

namespace App\Game\State;

final readonly class GameState
{
    public function __construct(
        public PlayerState $player1,
        public PlayerState $player2,
        public ?int $lastEventid,
    ) {}
}
