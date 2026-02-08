<?php

declare(strict_types=1);

namespace App\Game\State;

use App\Game\Player;

readonly class PlayerState
{
    /**
     * @param string[] $hand
     * @param string[] $drawPile
     */
    public function __construct(
        public Player $player,
        public array $hand,
        public array $drawPile,
    ) {}
}
