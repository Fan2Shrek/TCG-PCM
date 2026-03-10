<?php

declare(strict_types=1);

namespace App\Tests\Unit\Game\Card\Character;

use App\Enum\GameEventTypeEnum;
use App\Game\Card\Character\StonksCard;
use App\Game\GameContext;
use App\Tests\Unit\Game\Card\CardTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class StonksCardTest extends CardTestCase
{
    public function getCardFQCN(): string
    {
        return StonksCard::class;
    }

    public function testTurnStart()
    {
        $card = $this->getCard();
        $gameContext = $this->createGameContext();

        $card->onTurnStart($gameContext);
        $events = $gameContext->flushEvents();

        self::assertCount(1, $events);
        self::assertEquals(GameEventTypeEnum::COINS_GAINED, $events[0]->type);
    }

    #[DataProvider('provideTurnEndData')]
    public function testTurnEnd(int $expectedCoins, int $currentCoins)
    {
        $card = $this->getCard();
        $gameState = $this->createGameContext()->state;
        $gameState = $gameState->withUpdatedPlayer(
            $gameState->getCurrentPlayerState()->withUpdatedCoins($currentCoins)
        );
        $gameContext = new GameContext($gameState, $gameState->player1->player->id);

        $card->onTurnEnd($gameContext);
        $events = $gameContext->flushEvents();

        self::assertCount(1, $events);
        self::assertSame($expectedCoins, $events[0]->data['amount']);
    }

    public static function provideTurnEndData(): \Generator
    {
        yield '0 coins' => [0, 0];
        yield '5 coins' => [1, 5];
        yield '7 coins' => [2, 7];
        yield '10 coins' => [3, 10];
        yield '100 coins' => [7, 100];
    }
}
