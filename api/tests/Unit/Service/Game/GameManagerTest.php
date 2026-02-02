<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Game;

use App\Entity\Room;
use App\Entity\User;
use App\Enum\RoomStatusEnum;
use App\Service\Game\GameManager;
use App\Tests\Resources\InMemoryGameContextRepository;
use PHPUnit\Framework\TestCase;

final class GameManagerTest extends TestCase
{
    public function testRoomStartStatus()
    {
        $gm = new GameManager(new InMemoryGameContextRepository());
        $room = new Room($this->createStub(User::class));
        $room->setOpponent($this->createStub(User::class));

        $gm->startGame($room);

        self::assertSame(RoomStatusEnum::PLAYING, $room->getStatus());
    }
}
