<?php

declare(strict_types=1);

namespace App\Tests\Unit\Game\Card;

use App\Enum\GameEventTypeEnum;
use App\Game\Card\BenjaminCard;
use App\Game\State\GameEvent;

final class BenjaminCardTest extends CardTestCase
{
    public function testBenjaminCard(): void
    {
        $card = $this->getCard();
        $ctx = $this->createGameContext();

        $card->play($ctx, ['cards' => ['test_card']]);

        $events = $ctx->flushEvents();

        self::assertCount(1, $events);
        self::assertEquals(new GameEvent(0, GameEventTypeEnum::EFFECT_ADDED, GameEvent::GAME_EVENT, [
            'effect' => 'Hacked',
            'cardId' => 'test_card',
        ]), $events[0]);
    }

    protected function getCardFQCN(): string
    {
        return BenjaminCard::class;
    }
}
