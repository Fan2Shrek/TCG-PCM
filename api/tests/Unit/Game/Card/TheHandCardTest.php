<?php

namespace App\Tests\Unit\Game\Card;

use App\Enum\GameEventTypeEnum;
use App\Game\Card\TheHandCard;
use App\Game\State\PlayArea;

final class TheHandCardTest extends CardTestCase
{
    public function getCardFQCN(): string
    {
        return TheHandCard::class;
    }

    public function testCard()
    {
        $card = $this->getCard();
        $state = $this->createGameContext()->state;
        $state = $state->withUpdatedPlayer($state->player2->withPlayArea(new PlayArea(['card'])));
        $ctx = $this->createGameContext($state);

        $card->play($ctx);
        $events = $ctx->flushEvents();

        self::assertCount(2, $events);
        self::assertSame(GameEventTypeEnum::CARD_DISCARDED, $events[1]->type);
        self::assertSame('card', $events[1]->data['cardId']);
    }
}
