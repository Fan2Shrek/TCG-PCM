<?php

declare(strict_types=1);

namespace App\Tests\Unit\Game\Card\Monster;

use App\Game\Card\Monster\BFBCard;
use App\Tests\Unit\Game\Card\CardTestCase;

final class BFBCardTest extends CardTestCase
{
    public function getCardFQCN(): string
    {
        return BFBCard::class;
    }

    public function testStats()
    {
        $card = $this->getCard();

        self::assertSame(100, $card->getHealPoints());
        self::assertSame(20, $card->getBaseAttack());
        self::assertSame(20, $card->getAttack());
    }
}
