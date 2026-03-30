<?php

declare(strict_types=1);

namespace App\Service\Game\State;

use App\Game\State\GameState;
use App\Service\Game\GameStateRebuilder;

/**
 * This service ensure that we have an up to date gameState
 */
class GameStateProvider
{
    public function __construct(
        private GameStateRepositoryInterface $gameStateRepository,
        private GameEventRepositoryInterface $gameEventRepository,
        private GameStateRebuilder $gameStateRebuilder,
    ) {}

    public function get(string $id): ?GameState
    {
        $currentGameState = $this->gameStateRepository->get($id);

        if (null === $currentGameState) {
            return null;
        }

        return $this->buildGameStateFromEvents($currentGameState, $id);
    }

    private function buildGameStateFromEvents(GameState $gameState, string $id): GameState
    {
        $events = $this->gameEventRepository->getEventsSince($gameState->lastEventId, $id);

        if ([] === $events) {
            return $gameState;
        }

        $newState = $this->gameStateRebuilder->rebuild($gameState, $events);

        $this->gameStateRepository->save($newState, $id);

        return $newState;
    }
}
