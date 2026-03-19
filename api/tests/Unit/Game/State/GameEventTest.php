<?php

declare(strict_types=1);

namespace App\Tests\Unit\Game\State;

use App\Enum\GameEventTypeEnum;
use App\Game\State\GameEvent;
use PHPUnit\Framework\TestCase;

final class GameEventTest extends TestCase
{
    public function testGameEventCreation(): void
    {
        $event = new GameEvent(id: 1, type: GameEventTypeEnum::CARD_PLAYED, eventOrigin: GameEvent::PLAYER_EVENT, data: ['card' => 'Fireball', 'damage' => 5]);

        self::assertSame(1, $event->id);
        self::assertSame(GameEventTypeEnum::CARD_PLAYED, $event->type);
        self::assertSame(GameEvent::PLAYER_EVENT, $event->eventOrigin);
        self::assertSame(['card' => 'Fireball', 'damage' => 5], $event->data);
    }

    public function testPlayerEventCreation(): void
    {
        $event = GameEvent::player(type: GameEventTypeEnum::CARD_DRAWN, data: ['card' => 'Healing Potion']);

        self::assertSame(0, $event->id);
        self::assertSame(GameEventTypeEnum::CARD_DRAWN, $event->type);
        self::assertSame(GameEvent::PLAYER_EVENT, $event->eventOrigin);
        self::assertSame(['card' => 'Healing Potion'], $event->data);
    }
}
