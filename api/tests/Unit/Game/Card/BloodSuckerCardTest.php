<?php

namespace App\Tests\Unit\Game\Card;

use App\Game\Card\BloodSuckerCard;

final class BloodSuckerCardTest extends CardTestCase
{
    public function getCardFQCN(): string
    {
        return BloodSuckerCard::class;
    }

    public function testCardPlace()
    {
        $card = $this->getCard();
        $ctx = $this->createGameContext();

        $card->onCardPlace($ctx);
        $events = $ctx->flushEvents();

        self::assertCount(2, $events);
        self::assertSame('1', $events[0]->data['targetId']);
        self::assertSame('2', $events[1]->data['targetId']);
    }

    public function testCardTurnStart()
    {
        $card = $this->getCard();
        $ctx = $this->createGameContext();

        $card->onTurnStart($ctx);
        $events = $ctx->flushEvents();

        self::assertCount(2, $events);
        self::assertSame('1', $events[0]->data['targetId']);
        self::assertSame('2', $events[1]->data['targetId']);
    }
}
