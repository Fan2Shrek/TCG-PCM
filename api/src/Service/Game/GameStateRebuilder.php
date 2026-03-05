<?php

declare(strict_types=1);

namespace App\Service\Game;

use App\Enum\GameEventTypeEnum;
use App\Game\State\GameEvent;
use App\Game\State\GameState;
use App\Service\Game\Factory\ReplayableGameContextFactory;

class GameStateRebuilder
{
    public function __construct(
        private GameManager $gameManager,
    ) {}

    /**
     * @param GameEvent[] $events
     */
    public function rebuild(GameState $initial, array $events): GameState
    {
        $randomEvents = $this->filterRandomEvents($events);

        $this->gameManager->setGameContextFactory(new ReplayableGameContextFactory($randomEvents));

        $state = $initial;

        foreach ($events as $event) {
            $state = match ($event->type) {
                GameEventTypeEnum::CARD_PLAYED => $this->replayCard($event, $state),
                GameEventTypeEnum::TURN_ENDED => $this->replayEndTurn($event, $state),
                default => $this->gameManager->resolve($event, $state)->state,
            };
            $state = $state->withLastEventId($event->id);
        }

        return $state;
    }

    /**
     * @param GameEvent[] $events
     *
     * @return array<int|float|string>
     */
    private function filterRandomEvents(array &$events): array
    {
        $rolls = [];

        foreach ($events as $event) {
            if ($event->type === GameEventTypeEnum::DICE_ROLLED) {
                $rolls[] = $event->data['result'];
            } elseif ($event->type === GameEventTypeEnum::CARD_RUNTIME_VALUE) {
                $rolls[] = $event->data['value'];
            }
        }

        $events = array_filter(
            $events,
            static fn(GameEvent $event) => !\in_array($event->type, [GameEventTypeEnum::DICE_ROLLED, GameEventTypeEnum::CARD_RUNTIME_VALUE], true),
        );

        return $rolls;
    }

    private function replayCard(GameEvent $event, GameState $state): GameState
    {
        return $this->gameManager->resolve($event, $state)->state;
    }

    private function replayEndTurn(GameEvent $event, GameState $gameState): GameState
    {
        return $this->gameManager->resolve($event, $gameState)->state;
    }
}
