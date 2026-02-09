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
        public int $healthPoints,
        public array $hand,
        public array $drawPile,
    ) {}

    public function withUpdatedHealth(int $newHealth): self
    {
        return new self(player: $this->player, healthPoints: $newHealth, hand: $this->hand, drawPile: $this->drawPile);
    }

    /**
     * @param string[] $newHand
     * @param string[] $newDeck
     */
    public function withNewHandAndDeck(array $newHand, array $newDeck): self
    {
        return new self(player: $this->player, healthPoints: $this->healthPoints, hand: $newHand, drawPile: $newDeck);
    }
}
