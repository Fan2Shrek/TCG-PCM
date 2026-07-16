<?php

declare(strict_types=1);

namespace App\Game\State;

use App\Game\Card\CardState;
use App\Game\GameRandomizer;
use App\Game\Player;

final readonly class GameState
{
    public string $currentPlayer;

    public ?string $lastAddedCardId;

    /**
     * @var array<string, CardState> $cards
     */
    public array $cards;

    public GameRandomizer $randomizer;

    /**
     * @param array<string, CardState> $cards
     */
    public function __construct(
        public PlayerState $player1,
        public PlayerState $player2,
        public ?int $lastEventId,
        public int $seed,
        ?string $currentPlayer = null,
        array $cards = [],
    ) {
        $this->currentPlayer = $currentPlayer ?? $this->player1->player->id;
        $this->cards = $cards;

        $this->lastAddedCardId = null;
        $this->randomizer = new GameRandomizer($seed);
    }

    public function getPlayer(string $playerId): PlayerState
    {
        return $this->player1->player->id === $playerId ? $this->player1 : $this->player2;
    }

    /**
     * @return Player[]
     */
    public function getPlayers(): array
    {
        return [$this->player1->player, $this->player2->player];
    }

    public function getCurrentPlayerState(): PlayerState
    {
        return $this->currentPlayer === $this->player1->player->id ? $this->player1 : $this->player2;
    }

    public function getCurrentPlayer(): Player
    {
        return $this->getCurrentPlayerState()->player;
    }

    public function getOtherPlayerState(): PlayerState
    {
        return $this->getOtherPlayerStateById($this->currentPlayer);
    }

    public function getOtherPlayerStateById(string $id): PlayerState
    {
        return $id === $this->player1->player->id ? $this->player2 : $this->player1;
    }

    public function getNextPlayer(): Player
    {
        return $this->currentPlayer === $this->player1->player->id ? $this->player2->player : $this->player1->player;
    }

    public function isCurrentPlayer(Player $player): bool
    {
        return $this->currentPlayer === $player->id;
    }

    public function isFinished(): bool
    {
        return !$this->player1->isAlive() || !$this->player2->isAlive();
    }

    public function getCardState(string $cardId): ?CardState
    {
        return $this->cards[$cardId] ?? null;
    }

    /**
     * @return string[]
     */
    public function getAllActiveCards(): array
    {
        return array_merge($this->player1->playArea->getAll(), $this->player2->playArea->getAll(), [
            $this->player1->characterCardId,
            $this->player2->characterCardId,
        ]);
    }

    public function getLastAddedCardId(): ?string
    {
        return $this->lastAddedCardId;
    }

    /**
     * @return string[]
     */
    public function getAllMonsters(): array
    {
        return array_merge($this->player1->playArea->monsterCards, $this->player2->playArea->monsterCards);
    }

    #[\NoDiscard]
    public function withUpdatedPlayer(PlayerState $updatedPlayer): self
    {
        if ($this->player1->player->id === $updatedPlayer->player->id) {
            return clone($this, [
                'player1' => $updatedPlayer,
            ]);
        }

        if ($this->player2->player->id === $updatedPlayer->player->id) {
            return clone($this, [
                'player2' => $updatedPlayer,
            ]);
        }

        throw new \InvalidArgumentException(\sprintf('Player %s not found in GameState', $updatedPlayer->player->id));
    }

    #[\NoDiscard]
    public function withCurrentPlayer(string $currentPlayer): self
    {
        if (!\in_array($currentPlayer, [$this->player1->player->id, $this->player2->player->id], true)) {
            throw new \InvalidArgumentException(\sprintf('Player %s not found in GameState', $currentPlayer));
        }

        return clone($this, [
            'currentPlayer' => $currentPlayer,
        ]);
    }

    #[\NoDiscard]
    public function withLastEventId(int $lastEventId): self
    {
        return clone($this, [
            'lastEventId' => $lastEventId,
        ]);
    }

    #[\NoDiscard]
    public function withUpdatedCardState(CardState $card): self
    {
        $cards = $this->cards;
        $cards[$card->instanceId] = $card;

        return clone($this, [
            'cards' => $cards,
        ]);
    }

    #[\NoDiscard]
    public function resetCardState(string $cardId): self
    {
        $cards = $this->cards;

        $cards[$cardId] = $this->cards[$cardId]?->reset();

        return clone($this, [
            'cards' => $cards,
        ]);
    }

    #[\NoDiscard]
    public function addCard(CardState $card): self
    {
        $cards = $this->cards;
        $cards[$card->instanceId] = $card;

        return clone($this, [
            'cards' => $cards,
            'lastAddedCardId' => $card->instanceId,
        ]);
    }
}
