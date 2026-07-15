<?php

declare(strict_types=1);

namespace App\Tests\Unit\Game\Card\Monster;

use App\Game\Card\Monster\ClottyCard;
use App\Tests\Unit\Game\Card\CardTestCase;

final class ClottyCardTest extends CardTestCase
{
    public function getCardFQCN(): string
    {
        return ClottyCard::class;
    }

    public function testStats()
    {
        $card = $this->getCard();

        self::assertSame(7, $card->getHealPoints());
        self::assertSame(7, $card->getBaseAttack());
        self::assertSame(7, $card->getAttack());
    }
}
