<?php

declare(strict_types=1);

namespace App\Tests\Unit\Game\Card\Monster;

use App\Game\Card\Monster\RedBloonsMonsterCard;
use App\Tests\Unit\Game\Card\CardTestCase;

final class RedBloonsMonsterCardTest extends CardTestCase
{
    public function getCardFQCN(): string
    {
        return RedBloonsMonsterCard::class;
    }

    public function testBaseStats(): void
    {
        $card = $this->getCard();

        self::assertSame(5, $card->getBaseAttack());
        self::assertSame(10, $card->getHealPoints());
    }

    public function testNoOpLifecycleHooksProduceNoEvents(): void
    {
        $card = $this->getCard();
        $ctx = $this->createGameContext();

        $card->onMonsterPlayed($ctx);
        $card->onMonsterDeath($ctx);
        $card->onAttack($ctx);

        self::assertCount(0, $ctx->flushEvents());
    }
}
