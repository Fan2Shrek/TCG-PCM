<?php

declare(strict_types=1);

namespace App\Service\Game;

use App\Entity\Room;
use App\Game\GameContext;
use App\Service\Redis\RedisClient;

final class RedisGameContextRepository implements GameContextRepositoryInterface
{
    public function __construct(
        private RedisClient $redisClient,
    ) {}

    public function save(GameContext $gameContext, Room $room): void
    {
        $this->redisClient->set($this->getRedisKey($room), $gameContext);
    }

    public function get(Room $room): GameContext
    {
        return $this->redisClient->get($this->getRedisKey($room), GameContext::class);
    }

    private function getRedisKey(Room $room): string
    {
        return 'game_context_room_'.(string) $room->getId();
    }
}
