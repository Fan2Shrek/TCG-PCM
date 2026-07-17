<?php

namespace App\Tests\Unit\Game\Card\Character;

use App\Enum\GameEventTypeEnum;
use App\Game\Card\Character\NecromancianCard;
use App\Game\GameContext;
use App\Game\State\GameEvent;
use App\Tests\Unit\Game\Card\CardTestCase;

final class NecromancianCardTest extends CardTestCase
{
    public function getCardFQCN(): string
    {
        return NecromancianCard::class;
    }

    public function testOnTurnEnd()
    {
        $card = $this->getCard();
        $state = $this->createGameContext()->state;
        $state = $state->withUpdatedPlayer($state->player1->withDiscardedCard('id', 'template'))->withCurrentPlayer('2');
        $ctx = new GameContext($state, '1');

        $card->onTurnEnd($ctx);
        $events = $ctx->flushEvents();

        self::assertCount(2, $events);
        self::assertEquals(
            GameEvent::game(GameEventTypeEnum::CARD_REDRAWN, [
                'playerId' => '1',
                'cardId' => 'id',
            ]),
            $events[1],
        );
    }
}
