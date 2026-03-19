<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Game;

use App\Entity\Deck;
use App\Entity\Room;
use App\Entity\User;
use App\Enum\GameEventTypeEnum;
use App\Game\Card\AbstractPlayableCard;
use App\Game\Card\CardState;
use App\Game\Card\Character\AbstractCharacterCard;
use App\Game\Card\Interface\TurnAwareInterface;
use App\Game\Card\Trait\TurnAwareTrait;
use App\Game\Exception\NotEnoughCoinsException;
use App\Game\GameContext;
use App\Game\Player;
use App\Game\State\GameEvent;
use App\Game\State\GameState;
use App\Game\State\PlayArea;
use App\Game\State\PlayerState;
use App\Service\Game\CardFactory;
use App\Service\Game\CardRuntimeMap;
use App\Service\Game\Factory\GameContextFactory;
use App\Service\Game\GameEventApplier;
use App\Service\Game\GameEventApplierInterface;
use App\Service\Game\GameManager;
use App\Service\Game\State\GameEventRepositoryInterface;
use App\Service\Game\State\GameStateRepositoryInterface;
use App\Tests\Resources\MockCardRegistry;
use App\Tests\Unit\Fixtures\DummyCard;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Cache\CacheInterface;

final class GameManagerTest extends TestCase
{
    public function testPlayerStateDeck()
    {
        $gm = $this->getSut(true);
        $gameState = new GameState(
            new PlayerState(
                new Player('1', 'Player 1'),
                30,
                30,
                '',
                [],
                ['card1', 'card2', 'card3', 'card4', 'card5', 'card6', 'card7'],
                0,
                new PlayArea(),
            ),
            new PlayerState(
                new Player('2', 'Player 1'),
                30,
                30,
                '',
                [],
                ['card7', 'card8', 'card9', 'card10', 'card11', 'card12'],
                0,
                new PlayArea(),
            ),
            0,
            1,
            null,
            [

            ]
        );

        $events = $gm->startGame($gameState)->events;
        array_pop($events); // turn_started
        array_pop($events); // draw_card
        array_pop($events); // coins_gained

        self::assertCount(12, $events);
        $cardDrawnEvents = array_filter($events, fn (GameEvent $event) => $event->type === GameEventTypeEnum::CARD_DRAWN);
        self::assertCount(10, $cardDrawnEvents);
    }

    public function testCardPlayWithNoMoney()
    {
        self::expectException(NotEnoughCoinsException::class);
        self::expectExceptionMessage('Action cost 1 coins, got 0');

        $gm = $this->getSut();

        $gameState = $this->createGameState();
        $gameState = new GameState(
            $gameState->player1->withUpdatedCoins(0),
            $gameState->player2,
            $gameState->lastEventId,
            $gameState->seed,
            $gameState->currentPlayer,
            $gameState->cards,
        );
        $event = new GameEvent(
            0,
            GameEventTypeEnum::CARD_PLAYED,
            GameEvent::PLAYER_EVENT,
            ['playerId' => $gameState->player1->player->id, 'cardId' => 'card1'],
        );

        $gm->resolve($event, $gameState)->events;
    }

    public function testHandlePlayActionCallCard(): void
    {
        $gm = $this->getSut();

        $gameState = $this->createGameState();
        $event = new GameEvent(
            0,
            GameEventTypeEnum::CARD_PLAYED,
            GameEvent::PLAYER_EVENT,
            ['playerId' => $gameState->player1->player->id, 'cardId' => 'card2'],
        );

        $events = $gm->resolve($event, $gameState)->events;

        self::assertNotNull(SpyCard::$receivedContext);
        self::assertCount(3, $events);
        self::assertEquals([
                GameEventTypeEnum::CARD_PLAYED,
                GameEventTypeEnum::COINS_LOST,
                GameEventTypeEnum::CARD_DISCARDED,
        ], array_map(fn (GameEvent $event) => $event->type, $events)
        );
    }

    public function testEndTurn()
    {
        $gm = $this->getSut();

        $gameState = $this->createGameState();
        $event = new GameEvent(
            0,
            GameEventTypeEnum::TURN_ENDED,
            GameEvent::PLAYER_EVENT,
            ['playerId' => $gameState->player1->player->id],
        );

        $events = $gm->resolve($event, $gameState)->events;
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
            new GameEvent(
                0,
                GameEventTypeEnum::COINS_GAINED,
                GameEvent::GAME_EVENT,
                [
                    'playerId' => $gameState->player2->player->id,
                    'amount' => 3,
                ],
            ),
            new GameEvent(
                0,
                GameEventTypeEnum::CARD_DRAWN,
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
        $event = new GameEvent(
            0,
            GameEventTypeEnum::TURN_ENDED,
            GameEvent::PLAYER_EVENT,
            ['playerId' => $gameState->player2->player->id],
        );

        $events = $gm->resolve($event, $gameState)->events;
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
            new GameEvent(
                0,
                GameEventTypeEnum::COINS_GAINED,
                GameEvent::GAME_EVENT,
                [
                    'playerId' => $gameState->player1->player->id,
                    'amount' => 3,
                ],
            ),
            new GameEvent(
                0,
                GameEventTypeEnum::CARD_DRAWN,
                GameEvent::GAME_EVENT,
                ['playerId' => $gameState->player1->player->id],
            ),
        ];

        self::assertEquals($expected, $events);
    }

    public function testCardPlay()
    {
        $gm = $this->getSut();
        $gameState = $this->createGameState();

        $event = new GameEvent(
            0,
            GameEventTypeEnum::CARD_PLAYED,
            GameEvent::PLAYER_EVENT,
            [
                'playerId' => $gameState->player1->player->id,
                'cardId' => 'card2',
            ],
        );

        $resolution = $gm->resolve($event, $gameState);

        self::assertCount(3, $resolution->events);
    }

    public function testEndTurnCallTurnAwareCard()
    {
        $gm = $this->getSut();

        $gameState = $this->createGameState();
        $player = $gameState->player1->withPlayArea($gameState->player1->playArea->addPassiveCard('card1O'));
        $gameState = new GameState(
            $player,
            $gameState->player2,
            $gameState->lastEventId,
            $gameState->seed,
            $gameState->currentPlayer,
            [
                'card1O' => new CardState(
                    'card1O',
                    SpyCard::class,
                    '1',
                    [],
                ),
            ],
        );
        $event = new GameEvent(
            0,
            GameEventTypeEnum::TURN_ENDED,
            GameEvent::PLAYER_EVENT,
            ['playerId' => $gameState->player1->player->id],
        );

        $gm->resolve($event, $gameState)->events;

        self::assertTrue(SpyCard::$turnStartCalled);
    }

    public function testPropagate()
    {
        $gm = $this->getSut();

        $gameState = $this->createGameState();
        $event = new GameEvent(
            0,
            GameEventTypeEnum::TURN_ENDED,
            GameEvent::PLAYER_EVENT,
            ['playerId' => $gameState->player1->player->id],
        );

        $events = $gm->resolve($event, $gameState)->events;

        self::assertCount(4, $events);
    }

    private function createGameState(): GameState
    {
        $player1State = new PlayerState(
            new Player('1', 'Player 1', 67),
            30,
            30,
            '',
            [
                'card1',
                'card2',
            ],
            [
                'drawPile1' => DummyCard::class,
            ],
            10,
            new PlayArea(),
        );
        $player2State = new PlayerState(
            new Player('2', 'Player 2', 69),
            30,
            30,
            '',
            [],
            [
                'drawPile2' => DummyCard::class,
            ],
            10,
            new PlayArea(),
        );

        return new GameState(
            $player1State,
            $player2State,
            1,
            0,
            null,
            [
                'card1' => new CardState(
                    'card1',
                    DummyCard::class,
                    '1',
                    [],
                ),
                'card2' => new CardState(
                    'card2',
                    SpyCard::class,
                    '2',
                    [],
                ),
            ]
        );
    }

    private function createRoom(): Room
    {
        $owner = $this->createStub(User::class);
        $deck = new Deck($owner, 'test', DummyCharacterCard::class, array_fill(0, 10, DummyCard::class));
        $room = new Room($owner);
        $room->setOpponent($this->createStub(User::class));
        $room->setOwnerDeck($deck);
        $room->setOpponentDeck($deck);

        return $room;
    }

    private function getSut(bool $fakeGEA = false): GameManager
    {
        $gea = $fakeGEA ? new class implements GameEventApplierInterface {
            public function apply(GameEvent $event, GameState $gameState): GameState
            {
                return $gameState;
            }

            public function applyMultiple(array $events, GameState $gameState): GameState
            {
                return $gameState;
            }
        } : new GameEventApplier();

        return new GameManager(
            new CardRuntimeMap(
                new CardFactory(
                    new MockCardRegistry(
                        [
                            DummyCard::class => DummyCard::class,
                            'other_card' =>  DummyCard::class,
                            SpyCard::class => SpyCard::class,
                        ]
                    ),
                    new class implements CacheInterface {
                        public function get(string $name, callable $callable, ?float $beta = null, array &$metadata = null): mixed{
                            return $callable();
                        }

                        public function delete(string $key): bool
                        {
                            // no-op
                            return true;
                        }
                    }
                ),
            ),
            new GameContextFactory(),
            $gea,
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

    public function save(GameEvent $gameEvent, string $roomId): GameEvent
    {
        return $this->memory[] = $gameEvent;
    }

    public function getEventsSince(?int $lastEventId, string $roomId): array
    {
        return array_filter($this->memory, fn (GameEvent $event) => $event->id > $lastEventId);
    }
}

class SpyCard extends AbstractPlayableCard implements TurnAwareInterface
{
    use TurnAwareTrait;

    public static ?GameContext $receivedContext = null;
    public static bool $turnStartCalled = false;

    public function getId(): string
    {
        return self::class;
    }

    public function getName(): string
    {
        return 'Spy';
    }

    public function getDescription(): string
    {
        return $this->getName();
    }

    public function play(GameContext $ctx, array $data = []): void
    {
        self::$receivedContext = $ctx;

        if ($data['other'] ?? false) {
            $ctx->pushGameEvent(GameEventTypeEnum::CARD_PLAYED, ['playerId' => $ctx->playerId, 'cardId' => 'other-spy']);
        }
    }

    public function onTurnStart(GameContext $ctx): void
    {
        self::$turnStartCalled = true;
    }
}
