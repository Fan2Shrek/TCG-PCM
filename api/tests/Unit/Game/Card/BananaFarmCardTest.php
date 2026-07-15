<?php

declare(strict_types=1);

namespace App\Tests\Unit\Game\Card;

use App\Enum\GameEventTypeEnum;
use App\Game\Card\BananaFarmCard;

final class BananaFarmCardTest extends CardTestCase
{
    protected function getCardFQCN(): string
    {
        return BananaFarmCard::class;
    }

    public function testOnTurnStartGivesCoinsOnOwnerTurn(): void
    {
        $card = $this->getCard();
        $state = $this->createGameContext()->state;
        $state = $state->withCurrentPlayer('1');
        $ctx = $this->createGameContext($state);

        $card->onTurnStart($ctx);
        $events = $ctx->flushEvents();

        self::assertCount(1, $events);
        self::assertSame(GameEventTypeEnum::COINS_GAINED, $events[0]->type);
        self::assertSame(1, $events[0]->data['amount']);
    }

    public function testOnTurnStartDoesNothingOnOpponentTurn(): void
    {
        $card = $this->getCard();
        $state = $this->createGameContext()->state;
        $state = $state->withCurrentPlayer('2');
        $ctx = $this->createGameContext($state);

        $card->onTurnStart($ctx);
        $events = $ctx->flushEvents();

        self::assertCount(0, $events);
    }
}
