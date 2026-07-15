<?php

declare(strict_types=1);

namespace App\Tests\Unit\Game\Card\Monster;

use App\Game\Card\Monster\LeadBloonCard;
use App\Tests\Unit\Game\Card\CardTestCase;

final class LeadBloonCardTest extends CardTestCase
{
    public function getCardFQCN(): string
    {
        return LeadBloonCard::class;
    }

    public function testReduceDamageAppliesFlatReduction()
    {
        $card = $this->getCard();
        $ctx = $this->createGameContext();

        self::assertSame(5, $card->reduceDamage($ctx, 10));
    }

    public function testReduceDamageNeverGoesBelowZero()
    {
        $card = $this->getCard();
        $ctx = $this->createGameContext();

        self::assertSame(0, $card->reduceDamage($ctx, 3));
    }

    public function testBaseAttackAndHealPoints()
    {
        $card = $this->getCard();

        self::assertSame(10, $card->getBaseAttack());
        self::assertSame(10, $card->getHealPoints());
    }
}
