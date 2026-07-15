<?php

declare(strict_types=1);

namespace App\Tests\Unit\Game\Card\Monster;

use App\Game\Card\Monster\TheLostCard;
use App\Game\Card\MonsterCardState;
use App\Tests\Unit\Game\Card\CardTestCase;

final class TheLostCardTest extends CardTestCase
{
    public function getCardFQCN(): string
    {
        return TheLostCard::class;
    }

    public function testFirstDamageIsDodgedAndUpdatesState(): void
    {
        $card = $this->getCard();
        $ctx = $this->createGameContext();

        $reduced = $card->reduceDamage($ctx, 20);
        $events = $ctx->flushEvents();

        self::assertSame(0, $reduced);
        self::assertCount(1, $events);
        self::assertSame($card->getInstanceId(), $events[0]->data['cardId']);
        self::assertSame(['hasDodged' => true], $events[0]->data['stateToUpdate']);
    }

    public function testDamageIsNotDodgedAfterFirstDodge(): void
    {
        $card = new TheLostCard();
        $card->setState(new MonsterCardState('test_card', $card->getId(), '1', 1, [], ['hasDodged' => true]));
        $ctx = $this->createGameContext();

        $reduced = $card->reduceDamage($ctx, 20);
        $events = $ctx->flushEvents();

        self::assertSame(20, $reduced);
        self::assertCount(0, $events);
    }
}
