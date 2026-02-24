<?php

declare(strict_types=1);

namespace App\Game\State;

use App\Game\Player;

final readonly class GameState
{
    public string $currentPlayer;

    public function __construct(
        public PlayerState $player1,
        public PlayerState $player2,
        public ?int $lastEventid,
        ?string $currentPlayer = null,
    ) {
        $this->currentPlayer = $currentPlayer ?? $this->player1->player->id;
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

    public function withUpdatedPlayer(PlayerState $updatedPlayer): GameState
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

        throw new \LogicException(\sprintf('Player %s not found in GameState', $updatedPlayer->player->id));
    }

    public function withCurrentPlayer(string $currentPlayer): GameState
    {
        return clone($this, [
            'currentPlayer' => $currentPlayer,
        ]);
    }
}
