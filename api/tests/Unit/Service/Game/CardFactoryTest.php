<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Game;

use App\Game\Card\CardState;
use App\Service\Game\CardFactory;
use App\Tests\Resources\MockCardRegistry;
use App\Tests\Unit\Fixtures\DummyCard;
use PHPUnit\Framework\TestCase;

final class CardFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $factory = $this->getSut();
        $card = $factory->create(DummyCard::class);

        self::assertInstanceOf(DummyCard::class, $card);
    }

    public function testCreateTwice()
    {
        $factory = $this->getSut();
        $card1 = $factory->create(DummyCard::class);
        $card2 = $factory->create(DummyCard::class);

        self::assertInstanceOf(DummyCard::class, $card1);
        self::assertInstanceOf(DummyCard::class, $card2);
        self::assertNotSame($card1, $card2);
    }

    public function testCreateWithState()
    {
        $factory = $this->getSut();
        $state = new CardState('instanceId', DummyCard::class, 'ownerId', []);
        $card = $factory->createWithState(DummyCard::class, $state);

        self::assertInstanceOf(DummyCard::class, $card);
        self::assertSame('instanceId', $card->getInstanceId());
        self::assertSame('ownerId', $card->getOwnerId());
    }

    private function getSut(): CardFactory
    {
        return new CardFactory(new MockCardRegistry([
            DummyCard::class => DummyCard::class,
        ]));
    }
}
