<?php

declare(strict_types=1);

namespace App\Game\State;

use App\Game\Player;

readonly class PlayerState
{
    /**
     * @param string[] $hand
     * @param array<string, string> $drawPile
     * @param string[] $discardPile
     */
    public function __construct(
        public Player $player,
        public int $healthPoints,
        public int $maxHealthPoints,
        public string $characterCardId,
        public array $hand,
        public array $drawPile,
        public int $coins,
        public PlayArea $playArea,
        public array $discardPile = [],
    ) {}

    public function isAlive(): bool
    {
        return $this->healthPoints > 0;
    }

    public function hasCardInHand(string $cardId): bool
    {
        return \in_array($cardId, $this->hand, true);
    }

    #[\NoDiscard]
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
    #[\NoDiscard]
    public function withNewHandAndDeck(array $newHand, array $newDeck): self
    {
        return clone($this, [
            'hand' => $newHand,
            'drawPile' => $newDeck,
        ]);
    }

    #[\NoDiscard]
    public function removeCardFromHand(string $cardId): self
    {
        $hand = $this->hand;
        $key = array_search($cardId, $hand, true);

        if (false === $key) {
            throw new \BadMethodCallException(\sprintf('Card %s not found in hand', $cardId));
        }

        unset($hand[$key]);

        return clone($this, [
            'hand' => array_values($hand),
        ]);
    }

    #[\NoDiscard]
    public function withDiscardedCard(string $cardId): self
    {
        return clone($this, [
            'discardPile' => [...$this->discardPile, $cardId],
        ]);
    }

    #[\NoDiscard]
    public function withPlayArea(PlayArea $playArea): self
    {
        return clone($this, [
            'playArea' => $playArea,
        ]);
    }

    #[\NoDiscard]
    public function withUpdatedCoins(int $coins): self
    {
        return clone($this, [
            'coins' => $coins,
        ]);
    }
}
