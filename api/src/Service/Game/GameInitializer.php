<?php

declare(strict_types=1);

namespace App\Service\Game;

use App\Enum\GameEventTypeEnum;
use App\Game\State\GameEvent;
use App\Game\State\GameState;

class GameInitializer
{
    private const INITIAL_HAND_SIZE = 5;
    private const INITIAL_COINS = 5;

    public function __construct(
        private GameEventApplierInterface $gameEventApplier,
        private GameEventResolver $gameEventResolver,
    ) {}

    public function startGame(GameState $initialGameState): ResolutionResult
    {
        $events = [];
        foreach ($initialGameState->getPlayers() as $player) {
            for ($i = 0; $i < self::INITIAL_HAND_SIZE; $i++) {
                $events[] = GameEvent::game(GameEventTypeEnum::CARD_DRAWN, ['playerId' => $player->id]);
            }

            $events[] = GameEvent::game(GameEventTypeEnum::COINS_GAINED, [
                'playerId' => $player->id,
                'amount' => self::INITIAL_COINS,
            ]);
        }

        $state = $this->gameEventApplier->applyMultiple($events, $initialGameState);

        $roundStartedEvent = GameEvent::game(GameEventTypeEnum::TURN_STARTED, [
            'playerId' => $state->currentPlayer,
        ]);

        $result = $this->gameEventResolver->resolve($roundStartedEvent, $state);

        return new ResolutionResult(array_merge($events, $result->events), $result->state);
    }
}
