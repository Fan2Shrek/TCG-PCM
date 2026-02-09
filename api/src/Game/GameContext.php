<?php

declare(strict_types=1);

namespace App\Game;

use App\Enum\GameEventTypeEnum;
use App\Game\State\GameEvent;
use App\Game\State\GameState;

class GameContext
{
    /**
     * @var GameEvent[] $events
     */
    private array $events = [];

    public function __construct(
        public readonly GameState $state,
        public readonly string $playerId,
    ) {}

    public function drawCards(int $count, ?string $playerId = null): void
    {
        $playerId ??= $this->playerId;

        for ($i = 0; $i < $count; $i++) {
            $this->pushGameEvent(GameEventTypeEnum::CARD_DRAWN, ['playerId' => $playerId]);
        }
    }

    /**
     * @return GameEvent[]
     */
    public function flushEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }

    public function pushGameEvent(GameEventTypeEnum $type, array $payload = []): void
    {
        $this->events[] = GameEvent::game($type, $payload);
    }
}
