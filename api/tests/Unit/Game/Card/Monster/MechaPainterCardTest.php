<?php

declare(strict_types=1);

namespace App\Tests\Unit\Game\Card\Monster;

use App\Enum\GameEventTypeEnum;
use App\Game\Card\CardState;
use App\Game\Card\Monster\MechaPainterCard;
use App\Game\GameContext;
use App\Game\Player;
use App\Game\State\GameEvent;
use App\Game\State\GameState;
use App\Game\State\PlayArea;
use App\Game\State\PlayerState;
use App\Tests\Unit\Game\Card\CardTestCase;

final class MechaPainterCardTest extends CardTestCase
{
    public function getCardFQCN(): string
    {
        return MechaPainterCard::class;
    }

    public function testReduceDamageAppliesFlatReduction()
    {
        $card = $this->getCard();
        $ctx = $this->createGameContext();

        self::assertSame(35, $card->reduceDamage($ctx, 45));
    }

    public function testReduceDamageNeverGoesBelowZero()
    {
        $card = $this->getCard();
        $ctx = $this->createGameContext();

        self::assertSame(0, $card->reduceDamage($ctx, 5));
    }

    public function testOnTurnEndDamagesAnotherCardInOwnerPlayArea()
    {
        $card = $this->getCard();

        $player1State = new PlayerState(new Player('1', 'Player 1', 67), 30, 30, '', [], [], 0, new PlayArea(monsterCards: ['test_card', 'other_card']));
        $player2State = $this->createPlayerState('2');

        $gameState = new GameState($player1State, $player2State, 1, 0, null, [
            'test_card' => new CardState('test_card', $card->getId(), '1', []),
            'other_card' => new CardState('other_card', 'DummyCard', '1', []),
        ]);

        $gameContext = new GameContext($gameState, '1');

        $card->onTurnEnd(new GameEvent(0, GameEventTypeEnum::TURN_ENDED, GameEvent::PLAYER_EVENT, ['playerId' => $card->getOwnerId()]), $gameContext);
        $events = $gameContext->flushEvents();

        // selectRandomCardIn() also emits a CARD_RUNTIME_VALUE event before the damage event.
        self::assertCount(2, $events);
        self::assertSame(GameEventTypeEnum::CARD_RUNTIME_VALUE, $events[0]->type);
        self::assertSame('other_card', $events[0]->data['value']);
        self::assertSame(GameEventTypeEnum::DAMAGE, $events[1]->type);
        self::assertSame('other_card', $events[1]->data['targetId']);
        self::assertSame(10, $events[1]->data['damage']);
    }

    public function testOnTurnEndDoesNothingWhenNoOtherCardInPlayArea()
    {
        $card = $this->getCard();
        $ctx = $this->createGameContext();

        $card->onTurnEnd($this->createTurnEndedEvent('1'), $ctx);
        $events = $ctx->flushEvents();

        self::assertCount(0, $events);
    }

    public function testBaseAttackAndHealPoints()
    {
        $card = $this->getCard();

        self::assertSame(45, $card->getBaseAttack());
        self::assertSame(39, $card->getHealPoints());
    }
}
