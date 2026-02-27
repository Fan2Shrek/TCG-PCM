<?php

declare(strict_types=1);

namespace App\Debug;

use App\Game\State\GameEvent;
use Symfony\Bundle\FrameworkBundle\DataCollector\AbstractDataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
            'events' => $this->gameEventApplier->getEvents(),
        ];
    }

    /**
     * @return GameEvent[]
     */
    public function getEvents(): array
    {
        return $this->data['events'] ?? [];
    }

    public static function getTemplate(): ?string
    {
        return 'debug/game_events.html.twig';
    }
}
