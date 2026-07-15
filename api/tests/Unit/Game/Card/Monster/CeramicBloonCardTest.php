<?php

declare(strict_types=1);

namespace App\Tests\Unit\Game\Card\Monster;

use App\Game\Card\Monster\CeramicBloonCard;
use App\Tests\Unit\Game\Card\CardTestCase;

final class CeramicBloonCardTest extends CardTestCase
{
    public function getCardFQCN(): string
    {
        return CeramicBloonCard::class;
    }

    public function testStats()
    {
        $card = $this->getCard();

        self::assertSame(40, $card->getHealPoints());
        self::assertSame(8, $card->getBaseAttack());
        self::assertSame(8, $card->getAttack());
    }
}
