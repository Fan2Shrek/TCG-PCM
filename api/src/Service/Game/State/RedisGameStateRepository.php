<?php

declare(strict_types=1);

namespace App\Service\Game\State;

use App\Entity\Room;
use App\Game\State\GameState;
use App\Service\Redis\RedisClient;
use Symfony\Component\DependencyInjection\Attribute\WhenNot;

#[WhenNot('test')]
final class RedisGameStateRepository implements GameStateRepositoryInterface
{
    public function __construct(
        private RedisClient $redisClient,
        private GameStateRepositoryInterface $decoratedRepository,
    ) {
    }

    public function save(GameState $gameState, Room $room): void
    {
        $this->decoratedRepository->save($gameState, $room);
        $this->redisClient->set($this->getRedisKey($room), $gameState);
    }

    public function get(Room $room): GameState
    {
        if (!$gameState = $this->redisClient->get($this->getRedisKey($room), GameState::class) ) {
            $gameState = $this->decoratedRepository->get($room);
        }

        return $gameState;
    }

    private function getRedisKey(Room $room): string
    {
        return 'game_state'.(string) $room->getId();
    }
}
