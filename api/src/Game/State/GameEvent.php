<?php

declare(strict_types=1);

namespace App\Game\State;

use App\Enum\GameEventTypeEnum;

final readonly class GameEvent
{
    public const PLAYER_EVENT = 'player_event';
    public const GAME_EVENT = 'game_event';

    public function __construct(
        public int $id,
        public GameEventTypeEnum $type,
        public string $eventOrigin,
        public array $data,
    ) {}

    public static function game(GameEventTypeEnum $type, array $data): self
    {
        return new self(0, $type, self::GAME_EVENT, $data);
    }

    public static function player(GameEventTypeEnum $type, array $data): self
    {
        return new self(0, $type, self::PLAYER_EVENT, $data);
    }
}
