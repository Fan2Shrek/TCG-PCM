<?php

declare(strict_types=1);

namespace App\Debug;

use App\Debug\GameContext\DebugGameContext;
use App\Debug\GameContext\TraceableGameContextFactory;
use App\Game\State\GameEvent;
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
    ) {}

    public function collect(Request $request, Response $response, ?Throwable $exception = null): void
    {
        if (!$this->gameEventApplier->hasEvents() || !$this->gameContextFactory->hasGameContexts()) {
            return;
        }

        $this->data['mainEvent'] = $this->gameEventApplier->getEvents()[0];
        $this->data['subEvents'] = array_reduce(
            $this->gameContextFactory->getGameContexts(),
            fn(array $acc, DebugGameContext $gameContext) => array_merge($acc, $gameContext->flushedEvents),
            [],
        );

        $events = array_merge($this->gameEventApplier->getEvents(), $this->data['subEvents']);

        $this->data['events'] = $this->formatEvents($events);
        $this->data['gameContexts'] = $this->gameContextFactory->getGameContexts();

        $this->data = $this->cloneVar($this->data);
    }

    /**
     * @return Data|GameEvent[]
     */
    public function getEvents(): Data|array
    {
        return $this->data['events'] ?? [];
    }

    public function getEventsCount(): int
    {
        return count($this->getEvents());
    }

    public function getGameContexts(): Data|array
    {
        return $this->data['gameContexts'] ?? [];
    }

    public function getMainEvent(): Data|GameEvent|null
    {
        return $this->data['mainEvent'] ?? null;
    }

    public function getSubEvents(): Data|array
    {
        return $this->data['subEvents'] ?? [];
    }

    public static function getTemplate(): ?string
    {
        return 'debug/game_events.html.twig';
    }

    /**
     * Convert GameEvent objects to arrays because enum are fucked up when cloned
     *
     * @param GameEvent[] $events
     */
    private function formatEvents(array $events): array
    {
        return array_map(static fn(GameEvent $event) => [
            'id' => $event->id,
            'type' => $event->type->value,
            'origin' => $event->eventOrigin,
            'data' => $event->data,
        ], $events);
    }
}
