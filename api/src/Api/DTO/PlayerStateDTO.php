<?php

declare(strict_types=1);

namespace App\Api\DTO;

use App\Game\Player;
use App\Game\State\PlayArea;
use App\Game\State\PlayerState;

final class PlayerStateDTO
{
    /**
     * @param string[] $hand
     * @param array<string> $drawPile
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

    public static function fromPlayerState(PlayerState $playerState): static
    {
        return new static(
            player: $playerState->player,
            healthPoints: $playerState->healthPoints,
            maxHealthPoints: $playerState->maxHealthPoints,
            characterCardId: $playerState->characterCardId,
            hand: $playerState->hand,
            drawPile: array_keys($playerState->drawPile),
            coins: $playerState->coins,
            playArea: $playerState->playArea,
            discardPile: $playerState->discardPile,
        );
    }
}
