<?php

declare(strict_types=1);

namespace App\Tests\Unit\Game\Card\Monster;

use App\Game\Card\Monster\DartMonkeyCard;
use App\Tests\Unit\Game\Card\CardTestCase;

final class DartMonkeyCardTest extends CardTestCase
{
    public function getCardFQCN(): string
    {
        return DartMonkeyCard::class;
    }

    public function testStats()
    {
        $card = $this->getCard();

        self::assertSame(5, $card->getHealPoints());
        self::assertSame(10, $card->getBaseAttack());
        self::assertSame(10, $card->getAttack());
    }
}
