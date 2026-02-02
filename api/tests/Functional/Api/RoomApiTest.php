<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use App\Entity\Room;
use App\Tests\Functional\EntityAssertionTrait;
use App\Tests\Functional\FunctionalTestCase;
use App\Tests\Resources\Fixtures\ThereIs;

final class RoomApiTest extends FunctionalTestCase
{
    use EntityAssertionTrait;

    protected const CREATE_URI = '/api/rooms/create';
    protected const JOIN_URI = '/api/rooms/{id}/join';
    protected const START_URI = '/api/rooms/{id}/start';

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
        $room = ThereIs::aRoom()->build();

        $this->post($this->getUri(self::JOIN_URI, ['id' => (string) $room->getId()]));

        self::assertResponseIsSuccessful();
    }

    public function testJoinRoom()
    {
        $room = ThereIs::aRoom()->build();

        $this->post($this->getUri(self::JOIN_URI, ['id' => (string) $room->getId()]));

        self::assertSame($this->currentUser, $room->getOpponent());
    }

    public function testOwnerCannotJoinRoom()
    {
        $room = ThereIs::aRoom()->withOwner($this->currentUser)->build();

        $this->post($this->getUri(self::JOIN_URI, ['id' => (string) $room->getId()]));

        self::assertResponseStatusCodeSame(400);
    }

    public function testStartRoomSuccess()
    {
        $room = ThereIs::aRoom()
            ->withOwner($this->currentUser)
            ->withOpponent()
            ->build();

        $this->post($this->getUri(self::START_URI, ['id' => (string) $room->getId()]));

        self::assertResponseIsSuccessful();
    }

    public function testStartRoomFailedIfNotOwner()
    {
        $user = ThereIs::anUser()->build();
        $room = ThereIs::aRoom()
            ->withOwner($user)
            ->withOpponent()
            ->build()
        ;

        $this->post($this->getUri(self::START_URI, ['id' => (string) $room->getId()]));

        self::assertResponseStatusCodeSame(403);
    }

    public function testStartRoomFailedIfNoOpponent()
    {
        $room = ThereIs::aRoom()
            ->withOwner($this->currentUser)
            ->build();

        $this->post($this->getUri(self::START_URI, ['id' => (string) $room->getId()]));

        self::assertResponseStatusCodeSame(400);
    }
}
