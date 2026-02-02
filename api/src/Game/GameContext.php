<?php

declare(strict_types=1);

namespace App\Game;

class GameContext
{
    private bool $isPlayer1Turn = true;

    public function __construct(
        private Player $player1,
        private Player $player2,
    ) {}

    public function makePlayerDrawCards(int $count): void
    {
        $player = $this->getCurrentPlayer();
        $player->drawCard($count);
    }

    public function getCurrentPlayer(): Player
    {
        return $this->isPlayer1Turn ? $this->player1 : $this->player2;
    }

    public function nextPlayer(): void
    {
        $this->isPlayer1Turn = !$this->isPlayer1Turn;
    }

    public function getPlayers(): array
    {
        return [$this->player1, $this->player2];
    }
}
