<?php

declare(strict_types=1);

namespace App\Tests\Unit\Game\Card;

use App\Enum\GameEventTypeEnum;
use App\Game\Card\ConsolationPricesCard;
use App\Game\State\GameEvent;

final class ConsolationPricesCardTest extends CardTestCase
{
    public function getCardFQCN(): string
    {
        return ConsolationPricesCard::class;
    }

    public function testOnCardDeath(): void
    {
        $card = $this->getCard();
        $gameContext = $this->createGameContext();
        $stub = $this->createStubCard();
        $stub->method('getOwnerId')->willReturn('1');

        $card->onCardDeath($stub, $gameContext);
        $events = $gameContext->flushEvents();

        self::assertCount(1, $events);
        self::assertEquals(new GameEvent(0, GameEventTypeEnum::COINS_GAINED, GameEvent::GAME_EVENT, [
            'playerId' => '1',
            'amount' => 1,
        ]), $events[0]);
    }

    public function testOnCardDeathOtherPlayer(): void
    {
        $card = $this->getCard();
        $gameContext = $this->createGameContext();
        $stub = $this->createStubCard();
        $stub->method('getOwnerId')->willReturn('0');

        $card->onCardDeath($stub, $gameContext);
        $events = $gameContext->flushEvents();

        self::assertCount(0, $events);
    }
}
