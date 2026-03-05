<?php

declare(strict_types=1);

namespace App\Service\Game;

use App\Enum\GameEventTypeEnum;
use App\Game\State\GameEvent;
use App\Game\State\GameState;

class GameStateRebuilder
{
    public function __construct(
        private GameEventApplierInterface $applier,
        private GameManager $gameManager,
    ) {}

    /**
     * @param GameEvent[] $events
     */
    public function rebuild(GameState $initial, array $events): GameState
    {
        $state = $initial;

        foreach ($events as $event) {
            $state = match ($event->type) {
                GameEventTypeEnum::CARD_PLAYED => $this->replayCard($event, $state),
                default => $this->applier->apply($event, $state),
            };
            $state = $state->withLastEventId($event->id);
        }

        return $state;
    }

    private function replayCard(GameEvent $event, GameState $state): GameState
    {
        $events = $this->gameManager->getEventsForPlayedCard($event, $state);

        return $this->applier->applyMultiple(array_merge([$event], $events), $state);
    }
}
