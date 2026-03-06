<?php

declare(strict_types=1);

namespace App\Game;

final readonly class PlayerAction
{
    public const string PLAY_CARD = 'play_card';
    public const string ATTACK = 'attack';
    public const string END_TURN = 'end_turn';

    public const array ACTIONS = [
        self::PLAY_CARD,
        self::END_TURN,
        self::ATTACK,
    ];

    public function __construct(
        public Player $author,
        public string $actionId,
        public array $payload,
    ) {}
}
