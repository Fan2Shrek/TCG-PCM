<?php

namespace App\Tests\Unit\Game\Card\Monster;

use App\Game\Card\Monster\ViciousBeeCard;
use App\Tests\Unit\Game\Card\CardTestCase;

final class ViciousBeeCardTest extends CardTestCase
{
    public function getCardFQCN(): string
    {
        return ViciousBeeCard::class;
    }

    public function testCardDeath()
    {
        $card = $this->getCard();
        $ctx = $this->createGameContext();

        $card->onMonsterDeath($ctx);
        $events = $ctx->flushEvents();

        self::assertCount(1, $events);
        self::assertSame('ViciousStinger', $events[0]->data['cardId']);
        self::assertSame($card->getOwnerId(), $events[0]->data['playerId']);
    }
}
