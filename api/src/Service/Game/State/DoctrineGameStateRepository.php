<?php

declare(strict_types=1);

namespace App\Service\Game\State;

use App\Entity\Game\InitialGameState;
use App\Game\State\GameState;
use App\Repository\Game\InitialGameStateRepository;

final class DoctrineGameStateRepository implements GameStateRepositoryInterface
{
    public function __construct(
        private InitialGameStateRepository $initialGameStateRepository,
    ) {}

    public function save(GameState $gameState, string $room): void
    {
        $gameState = InitialGameState::createFromRoomAndGameState($room, $gameState);

        if ($this->initialGameStateRepository->count(['id' => $room])) {
            // Initial game state already exists for this room, we don't want to override it
            return;
        }

        $this->initialGameStateRepository->save($gameState);
    }

    public function get(string $room): ?GameState
    {
        $intialGameState = $this->initialGameStateRepository->find($room);

        return $intialGameState?->toGameState();
    }

    public function deleteAll(): void
    {
        $this->initialGameStateRepository->deleteAll();
    }
}
