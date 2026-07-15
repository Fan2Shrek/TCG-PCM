<?php

declare(strict_types=1);

namespace App\Tests\Unit\Game\Card\Monster;

use App\Game\Card\Monster\BlackBloonCard;
use App\Tests\Unit\Game\Card\CardTestCase;

final class BlackBloonCardTest extends CardTestCase
{
    public function getCardFQCN(): string
    {
        return BlackBloonCard::class;
    }

    public function testStats()
    {
        $card = $this->getCard();

        self::assertSame(15, $card->getHealPoints());
        self::assertSame(8, $card->getBaseAttack());
        self::assertSame(8, $card->getAttack());
    }

    public function testReduceDamageAbsorbsUpToReductionAmount()
    {
        $card = $this->getCard();
        $ctx = $this->createGameContext();

        self::assertSame(2, $card->reduceDamage($ctx, 10));
    }

    public function testReduceDamageNeverGoesNegative()
    {
        $card = $this->getCard();
        $ctx = $this->createGameContext();

        self::assertSame(0, $card->reduceDamage($ctx, 3));
    }
}
