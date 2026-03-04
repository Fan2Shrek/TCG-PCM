<?php

declare(strict_types=1);

namespace App\Debug;

use App\Debug\Card\TraceableCardRegistry;
use App\Debug\GameContext\TraceableGameContextFactory;
use App\Enum\GameEventTypeEnum;
use App\Game\AbstractCard;
use App\Game\State\GameEvent;
use App\Game\State\GameState;
use App\Game\State\PlayArea;
use Symfony\Bundle\FrameworkBundle\DataCollector\AbstractDataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\VarDumper\Cloner\Data;
use Throwable;

final class GameDataCollector extends AbstractDataCollector
{
    public function __construct(
        private TraceableGameEventApplier $gameEventApplier,
        private TraceableGameContextFactory $gameContextFactory,
        private TraceableCardRegistry $cardRegistry,
    ) {}

    public function collect(Request $request, Response $response, ?Throwable $exception = null): void
    {
        if ($exception) {
            return;
        }

        if (!$this->gameEventApplier->hasEvents() && !$this->gameContextFactory->hasGameContexts() && !$this->cardRegistry->hasCards()) {
            return;
        }

        $events = array_map(DebugGameEvent::fromTraceableGameEvent(...), $this->gameEventApplier->getEvents());

        $subEvents = array_filter($events, static fn($event) => !$event->isReplayEvent);
        $this->data['mainEvent'] = array_shift($subEvents);
        $this->data['subEvents'] = $subEvents;

        $this->data['stats'] = [
            'Player event' => count(array_filter($events, static fn(DebugGameEvent $event) => GameEvent::PLAYER_EVENT === $event->eventOrigin)),
            'Game event' => count(array_filter($events, static fn(DebugGameEvent $event) => GameEvent::GAME_EVENT === $event->eventOrigin)),
            'Replay event' => count(array_filter($events, static fn(DebugGameEvent $event) => $event->isReplayEvent)),
            'Total' => count($events),
        ];

        $this->data['events'] = $events;
        $this->data['gameContexts'] = $this->gameContextFactory->getGameContexts();

        $this->data['cards'] = $this->cardRegistry->getCards();

        $this->data['lastGameState'] = clone $this->gameEventApplier->getLastGameState();
    }

    /**
     * @return Data|GameEvent[]
     */
    public function getEvents(): array
    {
        return $this->data['events'] ?? [];
    }

    public function getEventsCount(): int
    {
        return count($this->getEvents());
    }

    public function getEventStats(): array
    {
        return $this->data['stats'] ?? [];
    }

    /**
     * @return Data|GameEvent[]
     */
    public function getGameContexts(): Data|array
    {
        return array_map(fn($a) => [
            'state' => $this->cloneVar($a->state),
            'flushedEvents' => $this->cloneVar($a->flushedEvents),
        ], $this->data['gameContexts'] ?? []);
    }

    public function getMainEvent(): ?DebugGameEvent
    {
        return $this->data['mainEvent'] ?? null;
    }

    /**
     * @return Data|GameEvent[]
     */
    public function getSubEvents(): array
    {
        return $this->data['subEvents'] ?? [];
    }

    /**
     * @return Data|AbstractCard[]
     */
    public function getCards(): array
    {
        return $this->data['cards'] ?? [];
    }

    public function getLastGameState(): ?GameState
    {
        return $this->data['lastGameState'] ?? null;
    }

    public function getLastCards(): Data|GameState|null
    {
        return $this->cloneVar($this->data['lastGameState']->cards) ?? null;
    }

    public function getPlayArea(string $playerId): Data|PlayArea|null
    {
        $playArea = $this->data['lastGameState']->getPlayer($playerId)->playArea ?? null;

        return $playArea ? $this->cloneVar($playArea) : null;
    }

    public function getReplayedEvents(): array
    {
        return array_filter($this->data['events'], static fn(DebugGameEvent $e) => $e->isReplayEvent);
    }

    public function getRealEvents(): array
    {
        return array_filter($this->data['events'], static fn(DebugGameEvent $e) => !$e->isReplayEvent);
    }

    public static function getTemplate(): ?string
    {
        return 'debug/game_events.html.twig';
    }
}

readonly class DebugGameEvent
{
    public function __construct(
        public string $origin,
        public GameEventTypeEnum $type,
        public string $eventOrigin,
        public array $data,
        public bool $shouldBePersisted,
        public bool $isReplayEvent,
    ) {}

    public static function fromTraceableGameEvent(TraceableGameEvent $event): self
    {
        return new self(
            $event->origin,
            $event->type,
            $event->eventOrigin,
            $event->data,
            $event->shouldBePersisted(),
            str_contains($event->origin, 'Rebuilder'),
        );
    }
}
