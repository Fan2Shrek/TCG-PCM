<?php

declare(strict_types=1);

namespace App\Service\Game\State;

use App\Game\State\GameState;
use App\Service\Redis\RedisClient;

final class RedisGameStateRepository implements GameStateRepositoryInterface
{
    public function __construct(
        private RedisClient $redisClient,
        private GameStateRepositoryInterface $decoratedRepository,
    ) {}

    public function save(GameState $gameState, string $room): void
    {
        $this->decoratedRepository->save($gameState, $room);
        $this->redisClient->set($this->getRedisKey($room), $gameState);
    }

    public function get(string $room): ?GameState
    {
        $gameState = $this->redisClient->get($this->getRedisKey($room), GameState::class);

        if (!$gameState instanceof GameState) {
            $gameState = $this->decoratedRepository->get($room);

            if ($gameState === null) {
                return null;
            }
        }

        $this->redisClient->set($this->getRedisKey($room), $gameState);

        return $gameState;
    }

    public function deleteAll(): void
    {
        if (method_exists($this->decoratedRepository, 'deleteAll')) {
            $this->decoratedRepository->deleteAll();
        }

        $this->redisClient->flushAll();
    }

    private function getRedisKey(string $room): string
    {
        return 'game_state'.$room;
    }
}
