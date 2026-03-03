<?php

declare(strict_types=1);

namespace App\Debug;

use App\Debug\Card\TraceableCardRegistry;
use App\Debug\GameContext\DebugGameContext;
use App\Debug\GameContext\TraceableGameContextFactory;
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

        $mainEvent = $this->gameEventApplier->getEvents()[0];
        $this->data['mainEvent'] = $mainEvent;
        $this->data['subEvents'] = array_reduce(
            $this->gameContextFactory->getGameContexts(),
            static fn(array $acc, DebugGameContext $gameContext) => array_merge($acc, $gameContext->flushedEvents),
            [],
        );

        $events = $this->gameEventApplier->getEvents();

        $this->data['stats'] = [
            'Player event' => count(array_filter($events, static fn(GameEvent $event) => GameEvent::PLAYER_EVENT === $event->eventOrigin)),
            'Game event' => count(array_filter($events, static fn(GameEvent $event) => GameEvent::GAME_EVENT === $event->eventOrigin)),
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
    public function getEvents(): Data|array
    {
        return $this->data['events'] ?? [];
    }

    public function getEventsCount(): int
    {
        return count($this->getEvents());
    }

    public function getEventStats(): Data|array
    {
        return $this->data['stats'] ?? [];
    }

    /**
     * @return Data|GameEvent[]
     */
    public function getGameContexts(): Data|array
    {
        return $this->cloneVar($this->data['gameContexts'] ?? []);
    }

    public function getMainEvent(): Data|GameEvent|null
    {
        return $this->data['mainEvent'] ?? null;
    }

    /**
     * @return Data|GameEvent[]
     */
    public function getSubEvents(): Data|array
    {
        return $this->data['subEvents'] ?? [];
    }

    /**
     * @return Data|AbstractCard[]
     */
    public function getCards(): Data|array
    {
        return $this->data['cards'] ?? [];
    }

    public function getLastGameState(): Data|GameState|null
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

    public static function getTemplate(): ?string
    {
        return 'debug/game_events.html.twig';
    }
}
