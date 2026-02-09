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
        return clone($this, [
            'healthPoints' => $newHealth,
        ]);
    }

    /**
     * @param string[] $newHand
     * @param string[] $newDeck
     */
    public function withNewHandAndDeck(array $newHand, array $newDeck): self
    {
        return clone($this, [
            'hand' => $newHand,
            'drawPile' => $newDeck,
        ]);
    }

    public function isAlive(): bool
    {
        return $this->healthPoints > 0;
    }

    public function hasCardInHand(string $cardId): bool
    {
        return \in_array($cardId, $this->hand, true);
    }
}
