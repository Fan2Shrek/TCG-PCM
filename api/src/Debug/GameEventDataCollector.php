<?php

declare(strict_types=1);

namespace App\Debug;

use App\Game\State\GameEvent;
use Symfony\Bundle\FrameworkBundle\DataCollector\AbstractDataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\VarDumper\Cloner\Data;
use Throwable;

final class GameEventDataCollector extends AbstractDataCollector
{
    public function __construct(
        private TraceableGameEventApplier $gameEventApplier,
    ) {}

    public function collect(Request $request, Response $response, ?Throwable $exception = null): void
    {
        if (!$this->gameEventApplier->hasEvents()) {
            return;
        }

        $this->data = [
            'events' => $this->formatEvents($this->gameEventApplier->getEvents()),
        ];

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
