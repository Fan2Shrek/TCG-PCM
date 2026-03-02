<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Game;

use App\Enum\CardEffectEnum;
use App\Game\State\GameEvent;
use App\Enum\GameEventTypeEnum;
use App\Game\Card\AbstractPlayableCard;
use App\Game\Card\CardState;
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

    public function testApplyCardDrawnUpdateGameState()
    {
        $eventApplier = $this->getSut();
        $state = $this->getInitialGameState();
        $event = new GameEvent(1, GameEventTypeEnum::CARD_DRAWN, GameEvent::PLAYER_EVENT, ['playerId' => 'player1']);

        $newState = $eventApplier->apply($event, $state);

        self::assertCount(1, $newState->cards);
        self::assertEquals(new CardState('id', 'D6', []), $newState->cards['id']);
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

    public function testEffectAdded()
    {
        $eventApplier = $this->getSut();
        $state = $this->getInitialGameState();
        $state = $state->addCard(new CardState('D6', 'D6', []));

        $event = new GameEvent(
            1,
            GameEventTypeEnum::EFFECT_ADDED,
            GameEvent::GAME_EVENT,
            [
                'cardId' => 'D6',
                'effect' => CardEffectEnum::HACKED->value,
            ]
        );

        $newState = $eventApplier->apply($event, $state);

        self::assertCount(1, $newState->cards['D6']->effects);
    }

    public function testCardDiscarded()
    {
        $eventApplier = $this->getSut();
        $state = $this->getInitialGameState();

        $event = new GameEvent(
            1,
            GameEventTypeEnum::CARD_DISCARDED,
            GameEvent::GAME_EVENT,
            [
                'playerId' => 'player2',
                'cardId' => 'D6',
            ]
        );

        $newState = $eventApplier->apply($event, $state);

        self::assertCount(0, $newState->getPlayer('player2')->hand);
        self::assertCount(1, $newState->getPlayer('player2')->discardPile);
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
                    'id' => 'D6',
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
                    'id' => 'D6',
                ],
            ),
            $lastEventId,
        );
    }
}
