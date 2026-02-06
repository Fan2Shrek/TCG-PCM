<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Game;

use App\Entity\Deck;
use App\Entity\Room;
use App\Entity\User;
use App\Enum\GameEventTypeEnum;
use App\Enum\RoomStatusEnum;
use App\Game\Card\Character\AbstractCharacterCard;
use App\Game\Player;
use App\Game\State\GameEvent;
use App\Game\State\GameState;
use App\Game\State\PlayerState;
use App\Service\Game\CardManager;
use App\Service\Game\GameManager;
use App\Service\Game\State\GameEventRepositoryInterface;
use App\Service\Game\State\GameStateRepositoryInterface;
use App\Tests\Resources\InMemoryGameStateRepository;
use PHPUnit\Framework\TestCase;

final class GameManagerTest extends TestCase
{
    public function testRoomStartStatus()
    {
        $gm = new GameManager(
            new InMemoryGameStateRepository(),
            new InmemoryGameEventRepository(),
            new CardManager(),
        );
        $room = $this->createRoom();

        $gm->startGame($room);

        self::assertSame(RoomStatusEnum::PLAYING, $room->getStatus());
    }

    public function testGameContextIsSavedOnStart()
    {
        $spyRepo = new SpyGameStateRepository();
        $gm = new GameManager(
            $spyRepo,
            new InmemoryGameEventRepository(),
            new CardManager(),
        );
        $room = $this->createRoom();

        $gm->startGame($room);

        self::assertNotNull($spyRepo->gameState);
    }

    public function testGameContextPlayers()
    {
        $spyRepo = new SpyGameStateRepository();
        $gm = new GameManager(
            $spyRepo,
            new InmemoryGameEventRepository(),
            new CardManager(),
        );
        $owner = new User('user', 'email');
        $opponent = new User('opponent', 'email2');
        $ownerDeck = new Deck($owner, 'test', DummyCharacterCard::class);
        $opponentDeck = new Deck($opponent, 'test', DummyCharacterCardWithMoreHP::class);
        $room = new Room($owner);
        $room->setOpponent($opponent);
        $room->setOwnerDeck($ownerDeck);
        $room->setOpponentDeck($opponentDeck);

        $gm->startGame($room);
        $gameState = $spyRepo->gameState;

        $expectedPlayer1 = new Player('user', 30);
        $expectedPlayer2 = new Player('opponent', 40);

        self::assertEquals($expectedPlayer1, $gameState->player1->player);
        self::assertEquals($expectedPlayer2, $gameState->player2->player);
    }

    public function testPlayFromRoom()
    {
        $gameState = new GameState(
            $this->createStub(PlayerState::class),
            $this->createStub(PlayerState::class),
            null,
        );
        $gameEvent = new GameEvent(1, GameEventTypeEnum::ATTACK, []);
        $room = $this->createStub(Room::class);
        $repository = new InMemoryGameStateRepository();
        $repository->save($gameState, $room);
        $gm = new class (
            $repository,
            new InmemoryGameEventRepository(),
            new CardManager(),
        ) extends GameManager {
            public bool $hasBeenCalled = false;
            public GameEvent $receivedEvent;
            public GameState $receivedGameState;

            public function play(GameEvent $event, GameState $gameState): void
            {
                $this->hasBeenCalled = true;
                $this->receivedEvent = $event;
                $this->receivedGameState = $gameState;
            }
        };

        $gm->playFromRoom($gameEvent, $room);

        self::assertTrue($gm->hasBeenCalled);
        self::assertSame($gameEvent, $gm->receivedEvent);
        self::assertSame($gameState, $gm->receivedGameState);
    }

    public function testGetGameState()
    {
        $gameState = new GameState(
            $this->createStub(PlayerState::class),
            $this->createStub(PlayerState::class),
            1,
        );
        $gameEvent = new GameEvent(4, GameEventTypeEnum::ATTACK, []);
        $room = $this->createStub(Room::class);
        $repository = new InMemoryGameStateRepository();
        $repository->save($gameState, $room);
        $events = [
            new GameEvent(2, GameEventTypeEnum::ATTACK, []),
            new GameEvent(3, GameEventTypeEnum::ATTACK, []),
        ];
        $gm = new class (
            $repository,
            new InMemoryGameEventRepository($events),
            new CardManager(),
        ) extends GameManager {
            public int $callCount = 0;
            public array $receivedEvent = [];

            public function play(GameEvent $event, GameState $gameState): void
            {
                $this->callCount++;
                $this->receivedEvent[] = $event;
            }
        };

        $gm->playFromRoom($gameEvent, $room);

        self::assertSame(3, $gm->callCount);
        self::assertSame(array_merge($events, [$gameEvent]), $gm->receivedEvent);
    }

    private function createRoom(): Room
    {
        $owner = $this->createStub(User::class);
        $deck = new Deck($owner, 'test', DummyCharacterCard::class);
        $room = new Room($owner);
        $room->setOpponent($this->createStub(User::class));
        $room->setOwnerDeck($deck);
        $room->setOpponentDeck($deck);

        return $room;
    }
}

class SpyGameStateRepository implements GameStateRepositoryInterface
{
    public function __construct(
        public ?GameState $gameState = null,
    ) {
    }

    public function save(GameState $gameContext, Room $room): void
    {
        $this->gameState = $gameContext;
    }

    public function get(Room $room): GameState
    {
        return $this->gameState;
    }
}

class InMemoryGameEventRepository implements GameEventRepositoryInterface
{
    public function __construct(
        public array $memory = [],
    ) {
    }

    public function save(GameEvent $gameEvent): void
    {
        $this->memory[] = $gameEvent;
    }

    public function getEventsSince(?int $lastEventId, string $roomId): array
    {
        return array_filter($this->memory, fn (GameEvent $event) => $event->id > $lastEventId);
    }
}

class DummyCharacterCard extends AbstractCharacterCard
{
    public function getHealthPoints(): int
    {
        return 30;
    }

    public function getName(): string
    {
        return 'Dummy';
    }

    public function getDescription(): string
    {
        return 'Dummy';
    }
}

class DummyCharacterCardWithMoreHP extends DummyCharacterCard
{
    public function getHealthPoints(): int
    {
        return 40;
    }
}
