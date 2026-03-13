<?php

declare(strict_types=1);

namespace App\Api\DTO;

final readonly class GameStateDTO
{
    /**
     * @param array<string, CardDTO> $cards
     */
    public function __construct(
        public PlayerStateDTO $player1,
        public PlayerStateDTO $player2,
        public string $currentPlayer,
        public array $cards,
    ) {}
}
