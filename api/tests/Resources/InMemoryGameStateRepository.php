<?php

declare(strict_types=1);

namespace App\Tests\Resources;

use App\Entity\Room;
use App\Game\State\GameState;
use App\Service\Game\State\GameStateRepositoryInterface;

final class InMemoryGameStateRepository implements GameStateRepositoryInterface
{
    private array $storage = [];

    public function save(GameState $gameContext, Room $room): void
    {
        $this->storage[spl_object_id($room)] = $gameContext;
    }

    public function get(Room $room): GameState
    {
        return $this->storage[spl_object_id($room)] ?? throw new \RuntimeException('Game State not found.');
    }
}
