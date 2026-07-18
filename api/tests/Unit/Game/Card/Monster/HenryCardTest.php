<?php

declare(strict_types=1);

namespace App\Tests\Unit\Game\Card\Monster;

use App\Enum\GameEventTypeEnum;
use App\Game\Card\Monster\HenryCard;
use App\Game\GameContext;
use App\Game\State\GameEvent;
use App\Tests\Unit\Game\Card\CardTestCase;

final class HenryCardTest extends CardTestCase
{
    public function getCardFQCN(): string
    {
        return HenryCard::class;
    }

    public function testOnTurnStartDiscardsCardOnOwnerTurn()
    {
        $card = $this->getCard();
        $ctx = $this->createGameContext();

        $card->onTurnStart(new GameEvent(0, GameEventTypeEnum::TURN_STARTED, GameEvent::PLAYER_EVENT, ['playerId' => $card->getOwnerId()]), $ctx);
        $events = $ctx->flushEvents();

        self::assertCount(1, $events);
        self::assertSame(GameEventTypeEnum::CARD_DISCARDED, $events[0]->type);
        self::assertSame($card->getInstanceId(), $events[0]->data['cardId']);
        self::assertSame($card->getOwnerId(), $events[0]->data['playerId']);
    }

    public function testOnTurnStartDoesNothingWhenNotOwnerTurn()
    {
        $card = $this->getCard();
        $gameState = $this->createGameContext()->state;
        $gameState = $gameState->withCurrentPlayer($gameState->player2->player->id);
        $gameContext = new GameContext($gameState, $gameState->player1->player->id);

        $card->onTurnStart(new GameEvent(0, GameEventTypeEnum::TURN_STARTED, GameEvent::PLAYER_EVENT, ['playerId' => '2']), $gameContext);
        $events = $gameContext->flushEvents();

        self::assertCount(0, $events);
    }

    public function testBaseAttackAndHealPoints()
    {
        $card = $this->getCard();

        self::assertSame(0, $card->getBaseAttack());
        self::assertSame(500, $card->getHealPoints());
    }
}
