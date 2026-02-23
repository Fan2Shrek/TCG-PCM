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

    public function attack(int $damage, ?string $playerId = null): void
    {
        $playerId ??= $this->getOpponent();

        $this->pushGameEvent(GameEventTypeEnum::DAMAGE, ['targetId' => $playerId, 'damage' => $damage]);
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

    public function getOpponent(): Player
    {
        return $this->state->player1->player->id === $this->playerId ? $this->state->player2->player : $this->state->player1->player;
    }

    public function rollDice(int $faces): int
    {
        $result = Dice::roll($faces);

        $this->events[] = GameEvent::game(GameEventTypeEnum::DICE_ROLLED, [
            'faces' => $faces,
            'result' => $result,
        ]);

        return $result;
    }
}
