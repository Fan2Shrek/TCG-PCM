<?php

declare(strict_types=1);

namespace App\Tests\Unit\Game\Card\Monster;

use App\Game\Card\Monster\MOABCard;
use App\Tests\Unit\Game\Card\CardTestCase;

final class MOABCardTest extends CardTestCase
{
    public function getCardFQCN(): string
    {
        return MOABCard::class;
    }

    public function testBaseAttackAndHealPoints()
    {
        $card = $this->getCard();

        self::assertSame(10, $card->getBaseAttack());
        self::assertSame(60, $card->getHealPoints());
    }

    public function testHasNoSpecialLifecycleEvents()
    {
        $card = $this->getCard();
        $ctx = $this->createGameContext();

        $card->onMonsterPlayed($ctx);
        $card->onMonsterDeath($ctx);
        $card->onAttack($ctx);

        self::assertCount(0, $ctx->flushEvents());
        self::assertSame(0, $card->reduceDamage($ctx, 0));
        self::assertSame(5, $card->reduceDamage($ctx, 5));
        self::assertTrue($card->canAttack());
    }
}
