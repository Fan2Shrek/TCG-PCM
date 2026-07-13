<?php

namespace App\Tests\Unit\Game\Card;

use App\Game\Card\CoinsCard;

final class CoinsCardTest extends CardTestCase
{
    public function getCardFQCN(): string
    {
        return CoinsCard::class;
    }

    public function testCard()
    {
        $card = $this->getCard();
        $ctx = $this->createGameContext();

        $card->play($ctx);
        $events = $ctx->flushEvents();

        self::assertCount(1, $events);
        self::assertSame(3, $events[0]->data['amount']);
    }
}
