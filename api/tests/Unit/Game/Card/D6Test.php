<?php

declare(strict_types=1);

namespace App\Tests\Unit\Game\Card;

use App\Enum\GameEventTypeEnum;
use App\Game\Card\D6Card;
use App\Game\State\GameEvent;
use PHPUnit\Framework\Attributes\DataProvider;

final class D6Test extends CardTestCase
{
    public function getCardFQCN(): string
    {
        return D6Card::class;
    }

    #[DataProvider('d6RollsProvider')]
    public function test6DrawCards(int $roll): void
    {
        $card = $this->getCard();
        $this->ensureNextDiceRolls($roll);
        $ctx = $this->createGameContext();

        $card->play($ctx);

        $events = $ctx->flushEvents();

        // 1 for the dice roll, $roll for the drawn cards
        self::assertCount($roll + 1, $events);
    }

    public function testD6Event(): void
    {
        $card = $this->getCard();
        $this->ensureNextDiceRolls(1);
        $ctx = $this->createGameContext();

        $card->play($ctx);

        [$rollEvent, $drawEvent] = $ctx->flushEvents();

        self::assertEquals(
            GameEvent::game(
                GameEventTypeEnum::DICE_ROLLED,
                [
                    'faces' => 6,
                    'result' => 1
                ],
            ),
            $rollEvent,
        );
        self::assertEquals(
            GameEvent::game(
                GameEventTypeEnum::CARD_DRAWN,
                ['playerId' => '1'],
            ),
            $drawEvent,
        );
    }

    public static function d6RollsProvider(): \Generator
    {
        yield from self::allRollFromGenerator(6);
    }
}
