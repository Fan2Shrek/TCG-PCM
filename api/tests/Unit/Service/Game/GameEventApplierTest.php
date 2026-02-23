<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Game;

use App\Game\State\GameEvent;
use App\Enum\GameEventTypeEnum;
use App\Game\Card\AbstractPlayableCard;
use App\Game\GameContext;
use App\Game\Player;
use App\Game\State\GameState;
use App\Game\State\PlayerState;
use App\Service\Game\GameEventApplier;
use App\Tests\Resources\MockCardRegistry;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\TestCase;

final class GameEventApplierTest extends TestCase
{
    #[Before]
    public function clearSpies(): void
    {
        SpyCard::$receivedContext = null;
        OtherSpyCard::$otherReceivedContext = null;
    }

    public function testApplyCardDrawn()
    {
        $eventApplier = new GameEventApplier(new MockCardRegistry([]));
        $state = $this->getInitialGameState();
        $event = new GameEvent(1, GameEventTypeEnum::CARD_DRAWN, GameEvent::PLAYER_EVENT, ['playerId' => 'player1']);

        $newState = $eventApplier->apply($event, $state);

        self::assertCount(1, $newState->player1->hand);
        self::assertSame('D6', $newState->player1->hand[0]);
    }

    public function testApplyCardDrawnWithPlayer2()
    {
        $eventApplier = new GameEventApplier(new MockCardRegistry([]));
        $state = $this->getInitialGameState();
        $event = new GameEvent(1, GameEventTypeEnum::CARD_DRAWN, GameEvent::PLAYER_EVENT, ['playerId' => 'player2']);

        $newState = $eventApplier->apply($event, $state);

        self::assertCount(0, $newState->player1->hand);
        self::assertCount(1, $newState->player2->hand);
    }

    public function testApplyCardPlayed(): void
    {
        $eventApplier = new GameEventApplier(new MockCardRegistry(
            [
                'spy' => SpyCard::class,
            ]
        ));
        $state = $this->getInitialGameState();
        $event = new GameEvent(
            1,
            GameEventTypeEnum::CARD_PLAYED,
            GameEvent::PLAYER_EVENT,
            [
                'playerId' => 'player2',
                'cardId' => 'spy',
            ]
        );

        $eventApplier->apply($event, $state);

        self::assertNotNull(SpyCard::$receivedContext);
    }

    public function testApplyCardPlayedApplyNewEvent(): void
    {
        $eventApplier = new GameEventApplier(new MockCardRegistry(
            [
                'spy' => SpyCard::class,
                'other-spy' => OtherSpyCard::class,
            ]
        ));
        $state = $this->getInitialGameState(2);
        $event = new GameEvent(
            1,
            GameEventTypeEnum::CARD_PLAYED,
            GameEvent::PLAYER_EVENT,
            [
                'playerId' => 'player2',
                'cardId' => 'spy',
            ]
        );

        $eventApplier->apply($event, $state);

        self::assertNotNull(SpyCard::$receivedContext);
        self::assertNotNull(OtherSpyCard::$otherReceivedContext);
    }

    public function testApplyDamage(): void
    {
        $eventApplier = new GameEventApplier(new MockCardRegistry([]));
        $state = $this->getInitialGameState();
        $event = new GameEvent(
            1,
            GameEventTypeEnum::DAMAGE,
            GameEvent::PLAYER_EVENT,
            [
                'targetId' => 'player2',
                'damage' => 15,
            ]
        );

        $newState = $eventApplier->apply($event, $state);

        self::assertSame(15, $newState->player2->healthPoints);
    }

    public function testApplyTurnEneded()
    {
        $eventApplier = new GameEventApplier(new MockCardRegistry([]));
        $state = $this->getInitialGameState();

        $event = new GameEvent(
            1,
            GameEventTypeEnum::TURN_ENDED,
            GameEvent::PLAYER_EVENT,
            [
                'playerId' => 'player2',
            ]
        );

        $newState = $eventApplier->apply($event, $state);

        self::assertSame('player2', $newState->currentPlayer);
    }

    private function getInitialGameState(int $lastEventId = 1): GameState
    {
        return new GameState(
            new PlayerState(
                new Player(
                    'player1',
                    'Alice',
                ),
                30,
                [],
                [
                    'D6',
                ],
            ),
            new PlayerState(
                new Player(
                    'player2',
                    'Bob',
                ),
                30,
                [],
                [
                    'D6',
                ],
            ),
            $lastEventId,
        );
    }
}

class SpyCard extends AbstractPlayableCard
{
    public static ?GameContext $receivedContext = null;

    public function getId(): string
    {
        return 'spy';
    }

    public function getName(): string
    {
        return 'Spy';
    }

    public function getDescription(): string
    {
        return $this->getName();
    }

    public function play(GameContext $ctx): void
    {
        self::$receivedContext = $ctx;

        if (2 === $ctx->state->lastEventid) {
            $ctx->pushGameEvent(GameEventTypeEnum::CARD_PLAYED, ['playerId' => $ctx->playerId, 'cardId' => 'other-spy']);
        }
    }
}

class OtherSpyCard extends SpyCard
{
    public static ?GameContext $otherReceivedContext = null;

    public function getId(): string
    {
        return 'other-spy';
    }

    public function play(GameContext $ctx): void
    {
        self::$otherReceivedContext = $ctx;
    }
}
