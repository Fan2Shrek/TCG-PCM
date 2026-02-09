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

    public function withUpdatedPlayer(PlayerState $updatedPlayer): GameState
    {
        if ($this->player1->player->id === $updatedPlayer->player->id) {
            return new GameState(
                player1: $updatedPlayer,
                player2: $this->player2,
                lastEventid: $this->lastEventid,
                currentPlayer: $this->currentPlayer,
            );
        }

        if ($this->player2->player->id === $updatedPlayer->player->id) {
            return new GameState(
                player1: $this->player1,
                player2: $updatedPlayer,
                lastEventid: $this->lastEventid,
                currentPlayer: $this->currentPlayer,
            );
        }

        throw new \LogicException(\sprintf('Player %s not found in GameState', $updatedPlayer->player->id));
    }

    public function getCurrentPlayerState(): PlayerState
    {
        return $this->currentPlayer === $this->player1->player->id ? $this->player1 : $this->player2;
    }

    public function getNextPlayer(): Player
    {
        return $this->currentPlayer === $this->player1->player->id ? $this->player2->player : $this->player1->player;
    }

    public function isCurrentPlayer(Player $player): bool
    {
        return $this->currentPlayer === $player->id;
    }
}
