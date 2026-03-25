<?php

declare(strict_types=1);

namespace App\Service\Game\State;

use App\Entity\Room;
use App\Game\State\GameState;
use App\Service\Game\GameStateRebuilder;
use App\Service\Redis\RedisClient;

final class RedisGameStateRepository implements GameStateRepositoryInterface
{
    public function __construct(
        private RedisClient $redisClient,
        private GameStateRepositoryInterface $decoratedRepository,
        private GameEventRepositoryInterface $gameEventRepository,
        private GameStateRebuilder $gameStateRebuilder,
    ) {}

    public function save(GameState $gameState, Room $room): void
    {
        $this->decoratedRepository->save($gameState, $room);
        $this->redisClient->set($this->getRedisKey($room), $gameState);
    }

    public function get(Room $room): ?GameState
    {
        $gameState = $this->redisClient->get($this->getRedisKey($room), GameState::class);

        if (!$gameState instanceof GameState) {
            $gameState = $this->decoratedRepository->get($room);

            if (!$gameState) {
                return null;
            }
        }

        $previousEventId = $gameState->lastEventId;
        $lastState = $this->buildGameStateFromEvents($gameState, $room);

        if ($previousEventId !== $lastState->lastEventId) {
            $this->redisClient->set($this->getRedisKey($room), $lastState);
        }

        return $lastState;
    }

    public function deleteAll(): void
    {
        if (method_exists($this->decoratedRepository, 'deleteAll')) {
            $this->decoratedRepository->deleteAll();
        }

        $this->redisClient->flushAll();
    }

    private function buildGameStateFromEvents(GameState $gameState, Room $room): GameState
    {
        $events = $this->gameEventRepository->getEventsSince($gameState->lastEventId, $room->getId()->toString());

        if ([] === $events) {
            return $gameState;
        }

        return $this->gameStateRebuilder->rebuild($gameState, $events);
    }

    private function getRedisKey(Room $room): string
    {
        return 'game_state'.(string) $room->getId();
    }
}
