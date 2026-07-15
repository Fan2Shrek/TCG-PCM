<?php

declare(strict_types=1);

namespace App\Tests\Unit\Game\Card\Monster;

use App\Game\Card\Monster\SniperMonkeyCard;
use App\Tests\Unit\Game\Card\CardTestCase;

final class SniperMonkeyCardTest extends CardTestCase
{
    public function getCardFQCN(): string
    {
        return SniperMonkeyCard::class;
    }

    public function testBaseStats(): void
    {
        $card = $this->getCard();

        self::assertSame(25, $card->getBaseAttack());
        self::assertSame(8, $card->getHealPoints());
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
