<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use App\Entity\Room;
use App\Tests\Functional\EntityAssertionTrait;
use App\Tests\Functional\FunctionalTestCase;

final class RoomApiTest extends FunctionalTestCase
{
    use EntityAssertionTrait;

    protected const URI = '/api/rooms/create';

    public function testCreateRoomSuccess()
    {
        $this->post(self::URI);

        self::assertResponseIsSuccessful();
    }

    public function testCreateRoomCreateRoom()
    {
        $this->post(self::URI);

        self::assertEntityCount(1, Room::class);
        $room = $this->getLastInsertedEntity();
        // TODO fix this assertion but the security service does not seem to
        // work with reusing login in clientTest::loginUser
        self::assertSame($this->currentUser->getUsername(), $room->getOwner()->getUsername());
    }
}
