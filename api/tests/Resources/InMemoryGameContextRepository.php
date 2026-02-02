<?php

declare(strict_types=1);

namespace App\Tests\Resources;

use App\Entity\Room;
use App\Game\GameContext;
use App\Service\Game\GameContextRepositoryInterface;

final class InMemoryGameContextRepository implements GameContextRepositoryInterface
{
    private array $storage = [];

    public function save(GameContext $gameContext, Room $room): void
    {
        $this->storage[spl_object_id($room)] = $gameContext;
    }

    public function get(Room $room): GameContext
    {
        return $this->storage[$room->getId()] ?? throw new \RuntimeException('Game context not found.');
    }
}
