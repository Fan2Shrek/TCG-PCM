<?php

declare(strict_types=1);

namespace App\Tests\Unit\Game\Card\Monster;

use App\Game\Card\Monster\Crypto4NoobCard;
use App\Tests\Unit\Game\Card\CardTestCase;

final class Crypto4NoobCardTest extends CardTestCase
{
    public function getCardFQCN(): string
    {
        return Crypto4NoobCard::class;
    }

    public function testGetHealPointsIsFixed()
    {
        $card = $this->getCard();

        self::assertSame(15, $card->getHealPoints());
    }

    public function testGetBaseAttackIsDerivedFromPrecomputedBitcoinPriceWithoutHttpCall()
    {
        $card = $this->getCard();
        $card->setComputedValue(30_000);

        self::assertSame(10, $card->getBaseAttack());
    }

    public function testComputeValueReturnsPrecomputedValueWithoutHttpCall()
    {
        $card = $this->getCard();
        $card->setComputedValue(12_345);

        self::assertSame(12_345, $card->computeValue());
    }
}
