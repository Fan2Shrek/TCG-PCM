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
}
