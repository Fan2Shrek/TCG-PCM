<?php

namespace App\Tests\Unit\Game\Card;

use App\Game\Card\StackyStackitoCard;
use App\Game\GameContext;
use PHPUnit\Framework\Attributes\DataProvider;

final class StackyStackitoCardTest extends CardTestCase
{
    public function getCardFQCN(): string
    {
        return StackyStackitoCard::class;
    }

    public function testCard()
    {
        $card = $this->getCard();
        $ctx = $this->createGameContext();

        for ($i = 0; $i < 9; $i++) {
            $card->onTurnStart($ctx);
        }
        $card->onTurnStart($ctx = $this->createGameContext());
        $events = $ctx->flushEvents();

        self::assertCount(2, $events);
    }

    #[DataProvider('provideCoins')]
    public function testCardDamage(int $coins)
    {
        $card = $this->getCard();
        $state = $this->createGameContext()->state;
        $state = $state->withUpdatedPlayer($state->getPlayer('1')->withUpdatedCoins($coins));
        $ctx = new GameContext($state, '1');

        for ($i = 0; $i < 9; $i++) {
            $card->onTurnStart($ctx);
        }
        $card->onTurnStart($ctx = new GameContext($state, '1'));
        $events = $ctx->flushEvents();

        self::assertSame($coins, $events[0]->data['damage']);
    }

    public static function provideCoins(): \Generator
    {
        yield [0];
        yield [10];
        yield [100];
    }
}
