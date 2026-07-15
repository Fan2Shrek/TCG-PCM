<?php

declare(strict_types=1);

namespace App\Tests\Unit\Game\Card\Monster;

use App\Game\Card\Monster\NinjaMonkeyCard;
use App\Game\Card\MonsterCardState;
use App\Tests\Unit\Game\Card\CardTestCase;

final class NinjaMonkeyCardTest extends CardTestCase
{
    public function getCardFQCN(): string
    {
        return NinjaMonkeyCard::class;
    }

    public function testFirstDamageIsDodgedAndUpdatesState(): void
    {
        $card = $this->getCard();
        $ctx = $this->createGameContext();

        $reduced = $card->reduceDamage($ctx, 15);
        $events = $ctx->flushEvents();

        self::assertSame(0, $reduced);
        self::assertCount(1, $events);
        self::assertSame($card->getInstanceId(), $events[0]->data['cardId']);
        self::assertSame(['hasDodged' => true], $events[0]->data['stateToUpdate']);
    }

    public function testDamageIsNotDodgedAfterFirstDodge(): void
    {
        $card = new NinjaMonkeyCard();
        $card->setState(new MonsterCardState('test_card', $card->getId(), '1', 8, [], ['hasDodged' => true]));
        $ctx = $this->createGameContext();

        $reduced = $card->reduceDamage($ctx, 15);
        $events = $ctx->flushEvents();

        self::assertSame(15, $reduced);
        self::assertCount(0, $events);
    }
}
