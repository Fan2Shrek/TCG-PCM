<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use App\Entity\Room;
use App\Entity\User;
use App\Tests\Functional\EntityAssertionTrait;
use App\Tests\Functional\FunctionalTestCase;

final class RoomApiTest extends FunctionalTestCase
{
    use EntityAssertionTrait;

    protected const CREATE_URI = '/api/rooms/create';
    protected const JOIN_URI = '/api/rooms/{id}/join';

    public function testCreateRoomSuccess()
    {
        $this->post(self::CREATE_URI);

        self::assertResponseIsSuccessful();
    }

    public function testCreateRoomCreateRoom()
    {
        $this->post(self::CREATE_URI);

        self::assertEntityCount(1, Room::class);
        $room = $this->getLastInsertedEntity();
        // TODO fix this assertion but the security service does not seem to
        // work with reusing login in clientTest::loginUser
        self::assertSame($this->currentUser->getUsername(), $room->getOwner()->getUsername());
    }

    public function testJoinRoomSuccess()
    {
        $owner = new User('owner');
        $owner->setPassword('blabla');
        $room = new Room($owner);
        self::getEM()->persist($owner);
        self::getEM()->persist($room);
        self::getEM()->flush();

        $this->post(str_replace('{id}', (string) $room->getId(), self::JOIN_URI));

        self::assertResponseIsSuccessful();
    }

    public function testJoinRoom()
    {
        $owner = new User('owner');
        $owner->setPassword('blabla');
        $room = new Room($owner);
        self::getEM()->persist($owner);
        self::getEM()->persist($room);
        self::getEM()->flush();

        $this->post(str_replace('{id}', (string) $room->getId(), self::JOIN_URI));

        self::assertSame($this->currentUser, $room->getOpponent());
    }

    public function testOwnerCannotJoinRoom()
    {
        $room = new Room($this->currentUser);
        self::getEM()->persist($room);
        self::getEM()->flush();

        $this->post(str_replace('{id}', (string) $room->getId(), self::JOIN_URI));

        self::assertResponseStatusCodeSame(400);
    }
}
