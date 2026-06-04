<?php

declare(strict_types=1);

namespace App\Tests\Unit\Game\Card;

use App\Game\Card\GlitchCard;

final class GlitchCardTest extends CardTestCase
{
    public function getCardFQCN(): string
    {
        return GlitchCard::class;
    }

    public function testGlitchCard(): void
    {
        $card = $this->getCard();
        $ctx = $this->createGameContext();

        $card->play($ctx);
        $events = $ctx->getEvents();

        self::assertCount(1, $events);
    }
}
