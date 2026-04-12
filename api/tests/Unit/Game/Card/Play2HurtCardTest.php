<?php

namespace App\Tests\Unit\Game\Card;

use App\Enum\GameEventTypeEnum;
use App\Game\Card\Play2HurtCard;

final class Play2HurtCardTest extends CardTestCase
{
    public function getCardFQCN(): string
    {
        return Play2HurtCard::class;
    }

    public function testCard()
    {
        $card = $this->getCard();
        $ctx = $this->createGameContext();
        $stub = $this->createStubCard();
        $stub->method('getOwnerId')->willReturn('target');

        $card->onCardPlayed($stub, $ctx);
        $events = $ctx->flushEvents();

        self::assertCount(1, $events);
        self::assertSame(GameEventTypeEnum::DAMAGE, $events[0]->type);
        self::assertSame('target', $events[0]->data['targetId']);
    }
}
