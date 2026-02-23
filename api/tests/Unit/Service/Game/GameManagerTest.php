<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Game;

use App\Entity\Deck;
use App\Entity\Room;
use App\Entity\User;
use App\Enum\GameEventTypeEnum;
use App\Enum\RoomStatusEnum;
use App\Game\Card\Character\AbstractCharacterCard;
use App\Game\Exception\CardNotInHandException;
use App\Game\Exception\UnknowActionException;
use App\Game\Player;
use App\Game\PlayerAction;
use App\Game\State\GameEvent;
use App\Game\State\GameState;
use App\Game\State\PlayerState;
use App\Service\Game\GameEventApplier;
use App\Service\Game\GameManager;
use App\Service\Game\State\GameEventRepositoryInterface;
use App\Service\Game\State\GameStateRepositoryInterface;
use App\Tests\Resources\MockCardRegistry;
use App\Tests\Unit\Fixtures\DummyCard;
use PHPUnit\Framework\TestCase;

final class GameManagerTest extends TestCase
{
    public function testRoomStartStatus()
    {
        $gm = $this->getSut();
        $room = $this->createRoom();

        $gm->startGame($room);

        self::assertSame(RoomStatusEnum::PLAYING, $room->getStatus());
    }

    public function testGameStatePlayers()
    {
        $gm = $this->getSut();
        $owner = new User('user', 'email');
        $opponent = new User('opponent', 'email2');
        $ownerDeck = new Deck($owner, 'test', DummyCharacterCard::class);
        $opponentDeck = new Deck($opponent, 'test', DummyCharacterCardWithMoreHP::class);
        $room = new Room($owner);
        $room->setOpponent($opponent);
        $room->setOwnerDeck($ownerDeck);
        $room->setOpponentDeck($opponentDeck);

        $gameState = $gm->startGame($room);

        $expectedPlayer1 = Player::fromUser($owner);
        $expectedPlayer2 = Player::fromUser($opponent);

        self::assertEquals($expectedPlayer1, $gameState->player1->player);
        self::assertEquals($expectedPlayer2, $gameState->player2->player);
    }

    public function testPlayerStateDeck()
    {
        $gm = $this->getSut();
        $owner = new TestUser('user', 'email');
        $opponent = new TestUser('opponent', 'email2');
        $ownerDeck = new Deck($owner, 'test', DummyCharacterCard::class);
        $ownerDeck->setCards(['card1', 'card2', 'card3', 'card4', 'card5', 'card6']);
        $opponentDeck = new Deck($opponent, 'test', DummyCharacterCardWithMoreHP::class);
        $opponentDeck->setCards(['card7', 'card8', 'card9', 'card10', 'card11', 'card12']);
        $room = new Room($owner);
        $room->setOpponent($opponent);
        $room->setOwnerDeck($ownerDeck);
        $room->setOpponentDeck($opponentDeck);

        $gameState = $gm->startGame($room);

        self::assertSame([
            'card1',
            'card2',
            'card3',
            'card4',
            'card5',
        ], $gameState->player1->hand);
        self::assertSame([
            'card6',
        ], $gameState->player1->drawPile);
        self::assertSame([
            'card7',
            'card8',
            'card9',
            'card10',
            'card11',
        ], $gameState->player2->hand);
        self::assertSame([
            'card12',
        ], $gameState->player2->drawPile);
    }

    public function testHandlePlayAction()
    {
        $gm = $this->getSut();

        $gameState = $this->createGameState();
        $action = new PlayerAction(
            $gameState->player1->player,
            PlayerAction::PLAY_CARD,
            ['cardId' => DummyCard::class],
        );

        $events = $gm->handleAction($action, $gameState);

        $expected = [
            new GameEvent(
                0,
                GameEventTypeEnum::CARD_PLAYED,
                GameEvent::PLAYER_EVENT,
                [
                    'playerId' => $gameState->player1->player->id,
                    'cardId' => DummyCard::class,
                ],
            ),
        ];

        self::assertEquals($expected, $events);
    }

    public function testHandlePlayActionWithCardNoInDeck()
    {
        $this->expectException(CardNotInHandException::class);
        $gm = $this->getSut();

        $gameState = $this->createGameState();
        $action = new PlayerAction(
            $gameState->player1->player,
            PlayerAction::PLAY_CARD,
            ['cardId' => 'other_card'],
        );

        $gm->handleAction($action, $gameState);
    }

    public function testHandleActionWithUnexistingAction()
    {
        $this->expectException(UnknowActionException::class);
        $gm = $this->getSut();

        $gameState = $this->createGameState();
        $action = new PlayerAction(
            $gameState->player1->player,
            'blablabla',
            ['cardId' => DummyCard::class],
        );

        $gm->handleAction($action, $gameState);
    }

    public function testEndTurn()
    {
        $gm = $this->getSut();

        $gameState = $this->createGameState();
        $action = new PlayerAction(
            $gameState->player1->player,
            PlayerAction::END_TURN,
            [],
        );

        $events = $gm->handleAction($action, $gameState);
        $expected = [
            new GameEvent(
                0,
                GameEventTypeEnum::TURN_ENDED,
                GameEvent::PLAYER_EVENT,
                ['playerId' => $gameState->player1->player->id],
            ),
            new GameEvent(
                0,
                GameEventTypeEnum::TURN_STARTED,
                GameEvent::GAME_EVENT,
                ['playerId' => $gameState->player2->player->id],
            ),
        ];

        self::assertEquals($expected, $events);
    }

    public function testEndTurnWithNewRound()
    {
        $gm = $this->getSut();

        $gameState = $this->createGameState();
        $gameState = $gameState->withCurrentPlayer(
            $gameState->player2->player->id,
        );
        $action = new PlayerAction(
            $gameState->player2->player,
            PlayerAction::END_TURN,
            [],
        );

        $events = $gm->handleAction($action, $gameState);
        $expected = [
            new GameEvent(
                0,
                GameEventTypeEnum::TURN_ENDED,
                GameEvent::PLAYER_EVENT,
                ['playerId' => $gameState->player2->player->id],
            ),
            new GameEvent(
                0,
                GameEventTypeEnum::ROUND_STARTED,
                GameEvent::GAME_EVENT,
                [],
            ),
            new GameEvent(
                0,
                GameEventTypeEnum::TURN_STARTED,
                GameEvent::GAME_EVENT,
                ['playerId' => $gameState->player1->player->id],
            ),
        ];

        self::assertEquals($expected, $events);
    }

    private function createGameState(): GameState
    {
        $player1State = new PlayerState(
            new Player('1', 'Player 1', 67),
            30,
            [
                DummyCard::class,
            ],
            [],
        );
        $player2State = new PlayerState(
            new Player('2', 'Player 2', 69),
            30,
            [],
            [],
        );

        return new GameState(
            $player1State,
            $player2State,
            1,
        );
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

    private function getSut(): GameManager
    {
        return new GameManager(
            $mock = new MockCardRegistry(
                [
                    DummyCard::class => DummyCard::class,
                    'other_card' =>  DummyCard::class,
                    DummyCharacterCard::class => DummyCharacterCard::class,
                    DummyCharacterCardWithMoreHP::class => DummyCharacterCardWithMoreHP::class,
                ]
            ),
            new GameEventApplier($mock),
        );
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

    public function getId(): string
    {
        return '';
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

class TestUser extends User
{
    public function getId(): int
    {
        return spl_object_id($this);
    }
}
