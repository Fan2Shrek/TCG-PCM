<?php

declare(strict_types=1);

namespace App\Tests\Unit\Game\Card\Monster;

use App\Enum\GameEventTypeEnum;
use App\Game\Card\Monster\GrilledClottyCard;
use App\Tests\Unit\Game\Card\CardTestCase;

final class GrilledClottyCardTest extends CardTestCase
{
    public function getCardFQCN(): string
    {
        return GrilledClottyCard::class;
    }

    public function testOnTurnEndDamagesItself()
    {
        $card = $this->getCard();
        $ctx = $this->createGameContext();

        $card->onTurnEnd($ctx);
        $events = $ctx->flushEvents();

        self::assertCount(1, $events);
        self::assertSame(GameEventTypeEnum::DAMAGE, $events[0]->type);
        self::assertSame($card->getInstanceId(), $events[0]->data['targetId']);
        self::assertSame(1, $events[0]->data['damage']);
    }

    public function testOnTurnStartDoesNothing()
    {
        $card = $this->getCard();
        $ctx = $this->createGameContext();

        $card->onTurnStart($ctx);
        $events = $ctx->flushEvents();

        self::assertCount(0, $events);
    }

    public function testBaseAttackAndHealPoints()
    {
        $card = $this->getCard();

        self::assertSame(21, $card->getBaseAttack());
        self::assertSame(7, $card->getHealPoints());
    }
}
