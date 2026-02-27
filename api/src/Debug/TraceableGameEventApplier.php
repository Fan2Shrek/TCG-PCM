<?php

declare(strict_types=1);

namespace App\Debug;

use App\Game\State\GameEvent;
use App\Game\State\GameState;
use App\Service\Game\GameEventApplierInterface;
use Symfony\Component\Stopwatch\Stopwatch;

final class TraceableGameEventApplier implements GameEventApplierInterface
{
    /**
     * @var GameEvent[] $event
     */
    private array $events = [];

    public function __construct(
        private GameEventApplierInterface $decorated,
        private Stopwatch $stopwatch,
    ) {}

    public function apply(GameEvent $event, GameState $gameState): GameState
    {
        $this->addEvent($event);

        $this->stopwatch->start('game_event_apply', 'game_event');

        $result = $this->decorated->apply($event, $gameState);

        $this->stopwatch->stop('game_event_apply');

        return $result;
    }

    public function applyMultiple(array $events, GameState $gameState): GameState
    {
        foreach ($events as $event) {
            $gameState = $this->apply($event, $gameState);
        }

        return $gameState;
    }

    public function hasEvents(): bool
    {
        return [] !== $this->events;
    }

    /**
     * @return GameEvent[]
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    private function addEvent(GameEvent $event): void
    {
        if (\in_array($event, $this->events, true)) {
            return;
        }

        $this->events[] = $event;
    }
}
