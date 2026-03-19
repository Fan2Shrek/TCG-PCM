<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Game;

use App\Enum\GameEventTypeEnum;
use App\Game\Player;
use App\Game\State\GameEvent;
use App\Game\State\GameState;
use App\Game\State\PlayArea;
use App\Game\State\PlayerState;
use App\Service\Game\GameEventApplier;
use App\Service\Game\GameEventResolver;
use App\Service\Game\GameInitializer;
use App\Service\Game\ResolutionResult;
use PHPUnit\Framework\TestCase;

final class GameInitizalizerTest extends TestCase
{
    public function testPlayerStateDeck()
    {
        $ger = $this->createStub(GameEventResolver::class);
        $ger->method('resolve')->willReturnCallback(static fn($event, $state) => new ResolutionResult([$event], $state));
        $gae = $this->createStub(GameEventApplier::class);
        $gae->method('applyMultiple')->willReturnCallback(static fn($events, $state) => $state);

        $gameInitializer = new GameInitializer($gae, $ger);
        $gameState = new GameState(
            new PlayerState(new Player('1', 'Player 1'), 30, 30, '', [], [], 0, new PlayArea()),
            new PlayerState(new Player('2', 'Player 1'), 30, 30, '', [], [], 0, new PlayArea()),
            0,
            1,
            null,
            [],
        );

        $events = $gameInitializer->startGame($gameState)->events;

        self::assertCount(13, $events);
        self::assertCount(10, array_filter($events, static fn(GameEvent $event) => $event->type === GameEventTypeEnum::CARD_DRAWN));
        self::assertCount(2, array_filter($events, static fn(GameEvent $event) => $event->type === GameEventTypeEnum::COINS_GAINED));
        self::assertCount(1, array_filter($events, static fn(GameEvent $event) => $event->type === GameEventTypeEnum::TURN_STARTED));
    }
}
