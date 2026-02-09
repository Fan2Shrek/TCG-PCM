<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Game\State;

use App\Entity\Room;
use App\Enum\GameEventTypeEnum;
use App\Game\State\GameEvent;
use App\Game\State\GameState;
use App\Game\State\PlayerState;
use App\Service\Game\GameManager;
use App\Service\Game\State\GameEventRepositoryInterface;
use App\Service\Game\State\GameStateRepositoryInterface;
use App\Service\Game\State\RedisGameStateRepository;
use App\Service\Redis\RedisClient;
use PHPUnit\Framework\TestCase;

final class RedisGameStateRepositoryTest extends TestCase
{
    public function testGet()
    {
        $gameState = new GameState(
            $this->createStub(PlayerState::class),
            $this->createStub(PlayerState::class),
            null,
            '',
        );
        $gameEvent = new GameEvent(1, GameEventTypeEnum::ATTACK, GameEvent::PLAYER_EVENT, []);
        $room = $this->createStub(Room::class);
        $testableGameManager = new TestableGameManager();
        $sut = $this->createSut($testableGameManager, $room, $gameState, [$gameEvent]);
        $sut->get($room);

        self::assertNotEmpty($testableGameManager->receivedEvent);
        self::assertSame([$gameState], $testableGameManager->receivedGameState);
        self::assertSame([$gameEvent], $testableGameManager->receivedEvent);
    }

    public function testGetWithMultipleEvents()
    {
        $gameState = new GameState(
            $this->createStub(PlayerState::class),
            $this->createStub(PlayerState::class),
            1,
            '',
        );
        $gameEvent = new GameEvent(4, GameEventTypeEnum::ATTACK, GameEvent::PLAYER_EVENT, []);
        $events = [
            new GameEvent(2, GameEventTypeEnum::ATTACK, GameEvent::PLAYER_EVENT, []),
            new GameEvent(3, GameEventTypeEnum::ATTACK, GameEvent::PLAYER_EVENT, []),
        ];
        $room = $this->createStub(Room::class);
        $testableGameManager = new TestableGameManager();
        $allEvents = array_merge($events, [$gameEvent]);
        $sut = $this->createSut($testableGameManager, $room, $gameState, $allEvents);
        $sut->get($room);

        self::assertSame(3, $testableGameManager->callCount);
        self::assertSame(array_merge($events, [$gameEvent]), $testableGameManager->receivedEvent);
    }

    public function testGetWithExistingGameState()
    {
        $gameState = new GameState(
            $this->createStub(PlayerState::class),
            $this->createStub(PlayerState::class),
            null,
            '',
        );
        $gameEvent = new GameEvent(4, GameEventTypeEnum::ATTACK, GameEvent::PLAYER_EVENT, []);
        $events = [
            new GameEvent(2, GameEventTypeEnum::ATTACK, GameEvent::PLAYER_EVENT, []),
            new GameEvent(3, GameEventTypeEnum::ATTACK, GameEvent::PLAYER_EVENT, []),
        ];
        $room = $this->createStub(Room::class);
        $testableGameManager = new TestableGameManager();
        $allEvents = array_merge($events, [$gameEvent]);
        $sut = $this->createSut($testableGameManager, $room, $gameState, $allEvents, $gameState);
        $sut->get($room);

        self::expectNotToPerformAssertions();
    }

    private function createSut(TestableGameManager $testable, Room $room, GameState $gameState, array $events, ?GameState $initialGameState = null): RedisGameStateRepository
    {
        $repository = new InMemoryGameStateRepository();

        if ($initialGameState) {
            $repository->save($initialGameState, $room);
        }

        $repository->save($gameState, $room);
        $redisClient = $this->createStub(RedisClient::class);
        $redisClient->method('get')->willReturn(null);
        $gameEventRepository = $this->createStub(GameEventRepositoryInterface::class);
        $gameEventRepository->method('getEventsSince')->willReturn($events);

        return new RedisGameStateRepository(
            $redisClient,
            $repository,
            $gameEventRepository,
            $testable,
        );
    }
}

class TestableGameManager extends GameManager
{
    public int $callCount = 0;
    public array $receivedEvent = [];
    public array $receivedGameState = [];

    public function __construct() {}

    public function play(GameEvent $event, GameState $gameState): GameState
    {
        $this->callCount++;
        $this->receivedEvent[] = $event;
        $this->receivedGameState[] = $gameState;

        return $gameState;
    }
}

class InMemoryGameStateRepository implements GameStateRepositoryInterface
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
