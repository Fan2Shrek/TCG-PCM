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
use App\Service\Game\Factory\GameContextFactory;
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
        $eventApplier = $this->getSut();
        $state = $this->getInitialGameState();
        $event = new GameEvent(1, GameEventTypeEnum::CARD_DRAWN, GameEvent::PLAYER_EVENT, ['playerId' => 'player1']);

        $newState = $eventApplier->apply($event, $state);

        self::assertCount(1, $newState->player1->hand);
        self::assertSame('D6', $newState->player1->hand[0]);
    }

    public function testApplyCardDrawnWithPlayer2()
    {
        $eventApplier = $this->getSut();
        $state = $this->getInitialGameState();
        $event = new GameEvent(1, GameEventTypeEnum::CARD_DRAWN, GameEvent::PLAYER_EVENT, ['playerId' => 'player2']);

        $newState = $eventApplier->apply($event, $state);

        self::assertCount(0, $newState->player1->hand);
        self::assertCount(1, $newState->player2->hand);
    }

    public function testApplyCardPlayed(): void
    {
        $eventApplier = $this->getSut(
            [
                'spy' => SpyCard::class,
            ],
            new GameContextFactory(),
        );
        $state = $this->getInitialGameState(player2Hand: ['spy']);
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
        $eventApplier = $this->getSut(
            [
                'spy' => SpyCard::class,
                'other-spy' => OtherSpyCard::class,
            ],
        );
        $state = $this->getInitialGameState(2, ['spy', 'other-spy']);
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
        $eventApplier = $this->getSut();
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
        $eventApplier = $this->getSut();
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

    private function getSut(array $cards = []): GameEventApplier
    {
        return new GameEventApplier(
            new MockCardRegistry($cards),
            new GameContextFactory()
        );
    }

    private function getInitialGameState(int $lastEventId = 1, array $player2Hand = []): GameState
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
                $player2Hand,
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

        if (2 === $ctx->state->lastEventId) {
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
