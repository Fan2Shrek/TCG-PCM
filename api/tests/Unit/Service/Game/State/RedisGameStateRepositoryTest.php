<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Game\State;

use App\Entity\Room;
use App\Enum\GameEventTypeEnum;
use App\Game\State\GameEvent;
use App\Game\State\GameState;
use App\Game\State\PlayerState;
use App\Service\Game\GameStateRebuilder;
use App\Service\Game\State\GameEventRepositoryInterface;
use App\Service\Game\State\GameStateRepositoryInterface;
use App\Service\Game\State\RedisGameStateRepository;
use App\Service\Redis\RedisClient;
use PHPUnit\Framework\TestCase;

final class RedisGameStateRepositoryTest extends TestCase
{
    public function testGet()
    {
        $gameState = new GameState($this->createStub(PlayerState::class), $this->createStub(PlayerState::class), null, 0, '');
        $gameEvent = new GameEvent(1, GameEventTypeEnum::DAMAGE, GameEvent::PLAYER_EVENT, []);
        $room = $this->createStub(Room::class);
        $testableGameManager = new TestableGameStateRebuilder();
        $sut = $this->createSut($testableGameManager, $room, $gameState, [$gameEvent]);
        $sut->get((string) spl_object_id($room));

        self::assertNotEmpty($testableGameManager->receivedEvents);
        self::assertSame([$gameState], $testableGameManager->receivedGameState);
        self::assertSame([$gameEvent], $testableGameManager->receivedEvents);
    }

    public function testGetWithMultipleEvents()
    {
        $gameState = new GameState($this->createStub(PlayerState::class), $this->createStub(PlayerState::class), 1, 0, '');
        $gameEvent = new GameEvent(4, GameEventTypeEnum::DAMAGE, GameEvent::PLAYER_EVENT, []);
        $events = [
            new GameEvent(2, GameEventTypeEnum::DAMAGE, GameEvent::PLAYER_EVENT, []),
            new GameEvent(3, GameEventTypeEnum::DAMAGE, GameEvent::PLAYER_EVENT, []),
        ];
        $room = $this->createStub(Room::class);
        $testableGameManager = new TestableGameStateRebuilder();
        $allEvents = array_merge($events, [$gameEvent]);
        $sut = $this->createSut($testableGameManager, $room, $gameState, $allEvents);
        $sut->get((string) spl_object_id($room));

        self::assertSame(1, $testableGameManager->callCount);
        self::assertSame(array_merge($events, [$gameEvent]), $testableGameManager->receivedEvents);
    }

    public function testGetWithExistingGameState()
    {
        $gameState = new GameState($this->createStub(PlayerState::class), $this->createStub(PlayerState::class), null, 0, '');
        $gameEvent = new GameEvent(4, GameEventTypeEnum::DAMAGE, GameEvent::PLAYER_EVENT, []);
        $events = [
            new GameEvent(2, GameEventTypeEnum::DAMAGE, GameEvent::PLAYER_EVENT, []),
            new GameEvent(3, GameEventTypeEnum::DAMAGE, GameEvent::PLAYER_EVENT, []),
        ];
        $room = $this->createStub(Room::class);
        $testableGameManager = new TestableGameStateRebuilder();
        $allEvents = array_merge($events, [$gameEvent]);
        $sut = $this->createSut($testableGameManager, $room, $gameState, $allEvents, $gameState);
        $sut->get((string) spl_object_id($room));

        self::expectNotToPerformAssertions();
    }

    private function createSut(
        TestableGameStateRebuilder $testable,
        Room $room,
        GameState $gameState,
        array $events,
        ?GameState $initialGameState = null,
    ): RedisGameStateRepository {
        $repository = new InMemoryGameStateRepository();

        if ($initialGameState) {
            $repository->save($initialGameState, (string) spl_object_id($room));
        }

        $repository->save($gameState, (string) spl_object_id($room));
        $redisClient = $this->createStub(RedisClient::class);
        $redisClient->method('get')->willReturn(null);
        $gameEventRepository = $this->createStub(GameEventRepositoryInterface::class);
        $gameEventRepository->method('getEventsSince')->willReturn($events);

        return new RedisGameStateRepository($redisClient, $repository, $gameEventRepository, $testable);
    }
}

class TestableGameStateRebuilder extends GameStateRebuilder
{
    public int $callCount = 0;
    public array $receivedEvents = [];
    public array $receivedGameState = [];

    public function __construct() {}

    public function rebuild(GameState $gameState, array $events): GameState
    {
        $this->callCount++;
        $this->receivedEvents = $events;
        $this->receivedGameState[] = $gameState;

        return $gameState;
    }
}

class InMemoryGameStateRepository implements GameStateRepositoryInterface
{
    private array $storage = [];

    public function save(GameState $gameContext, string $room): void
    {
        $this->storage[$room] = $gameContext;
    }

    public function get(string $room): GameState
    {
        return $this->storage[$room] ?? throw new \RuntimeException('Game State not found.');
    }
}
