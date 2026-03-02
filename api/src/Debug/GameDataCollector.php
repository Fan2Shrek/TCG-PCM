<?php

declare(strict_types=1);

namespace App\Debug;

use App\Debug\Card\TraceableCardRegistry;
use App\Debug\Card\TraceablePlayableCard;
use App\Debug\GameContext\DebugGameContext;
use App\Debug\GameContext\TraceableGameContextFactory;
use App\Game\AbstractCard;
use App\Game\Card\Effect\AbstractCardEffect;
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
        private TraceableCardRegistry $cardRegistry,
    ) {}

    public function collect(Request $request, Response $response, ?Throwable $exception = null): void
    {
        if (!$this->gameEventApplier->hasEvents() && !$this->gameContextFactory->hasGameContexts() && !$this->cardRegistry->hasCards()) {
            return;
        }

        $mainEvent = $this->gameEventApplier->getEvents()[0];
        $this->data['mainEvent'] = $this->formatEvents([$mainEvent])[0];
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

        $this->data['events'] = $this->formatEvents($events);
        $this->data['gameContexts'] = $this->gameContextFactory->getGameContexts();

        $this->data['cards'] = $this->formatCards($this->cardRegistry->getCards());

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

    public function getEventStats(): Data|array
    {
        return $this->data['stats'] ?? [];
    }

    /**
     * @return Data|GameEvent[]
     */
    public function getGameContexts(): Data|array
    {
        return $this->data['gameContexts'] ?? [];
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
            'line' => $event instanceof TraceableGameEvent ? $event->origin : 'unknown',
        ], $events);
    }

    /**
     * Convert AbstractCard objects to arrays because idk
     *
     * @param AbstractCard[] $cards
     */
    private function formatCards(array $cards): array
    {
        return array_map(static fn(AbstractCard $card) => [
            'id' => $card->getId(),
            'instanceId' => $card->getInstanceId(),
            'effects' => !$card instanceof TraceablePlayableCard ? null : array_map(static fn(AbstractCardEffect $effect) => [
                    'type' => $effect::getName()->value,
                    'properties' => get_object_vars($effect),
                ], $card->getEffects()->all()),
        ], $cards);
    }
}
