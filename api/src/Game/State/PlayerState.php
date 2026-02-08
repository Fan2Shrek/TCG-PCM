<?php

declare(strict_types=1);

namespace App\Game\State;

use App\Game\AbstractCard;
use App\Game\Player;

readonly class PlayerState
{
    /**
     * @param AbstractCard[] $hand
     * @param AbstractCard[] $drawPile
     */
    public function __construct(
        public Player $player,
        public array $hand,
        public array $drawPile,
    ) {}
}
