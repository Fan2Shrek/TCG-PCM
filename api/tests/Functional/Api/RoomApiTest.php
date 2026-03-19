<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use App\Entity\Deck;
use App\Entity\Game\InitialGameState;
use App\Entity\Room;
use App\Entity\User;
use App\Game\Card\Character\PierrotCard;
use App\Tests\Functional\EntityAssertionTrait;
use App\Tests\Functional\FunctionalTestCase;
use App\Tests\Resources\Fixtures\ThereIs;

final class RoomApiTest extends FunctionalTestCase
{
    use EntityAssertionTrait;

    protected const CREATE_URI = '/api/rooms/create';
    protected const JOIN_URI = '/api/rooms/{id}/join';
    protected const START_URI = '/api/rooms/{id}/start';
    protected const CHANGE_DECK_URI = '/api/rooms/{id}/change_deck';

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
        // @TODO fix this assertion but the security service does not seem to
        // work with reusing login in clientTest::loginUser
        self::assertSame($this->currentUser->getUsername(), $room->getOwner()->getUsername());
    }

    public function testCreateWithoutDeck()
    {
        $this->currentUser->removeDeck($this->currentUser->getDecks()[0]);
        $this->getEm()->flush();

        $this->post(self::CREATE_URI);

        self::assertResponseStatusCodeSame(400);
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

    public function testJoinRoomWithoutDeck()
    {
        $room = ThereIs::aRoom()->build();
        $this->currentUser->removeDeck($this->currentUser->getDecks()[0]);
        $this->getEm()->flush();

        $this->post($this->getUri(self::JOIN_URI, ['id' => (string) $room->getId()]));

        self::assertResponseStatusCodeSame(400);
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

    public function testGameStateInserted()
    {
        $room = ThereIs::aRoom()
            ->withOwner($this->currentUser)
            ->withOpponent()
            ->build();

        $this->post($this->getUri(self::START_URI, ['id' => (string) $room->getId()]));

        self::assertEntityCount(1, InitialGameState::class);
    }

    public function testStartRoomFailedIfNotOwner()
    {
        $user = ThereIs::anUser()->build();
        $room = ThereIs::aRoom()
            ->withOwner($user)
            ->withOpponent()
            ->build();

        $this->post($this->getUri(self::START_URI, ['id' => (string) $room->getId()]));

        self::assertResponseStatusCodeSame(403);
    }

    public function testStartRoomFailedIfNoOpponent()
    {
        $room = ThereIs::aRoom()->withOwner($this->currentUser)->build();

        $this->post($this->getUri(self::START_URI, ['id' => (string) $room->getId()]));

        self::assertResponseStatusCodeSame(400);
    }

    protected function createUser(?string $username = null, ?string $password = null): User
    {
        $user = new User($username ?? 'test');
        $user->setPassword(self::getContainer()->get('security.password_hasher')->hashPassword($user, $password ?? 'password'));
        $deck = new Deck($user, 'test deck', '');
        $deck->setCharacterCard(new PierrotCard()->getId());
        $user->addDeck($deck);
        $this->getEm()->persist($user);
        $this->getEm()->persist($deck);
        $this->getEm()->flush();

        return $user;
    }

    public function testChangeDeckSuccess()
    {
        $room = ThereIs::aRoom()->withOwner($this->currentUser)->build();

        $deck = $this->currentUser->getDecks()[0];

        $this->post($this->getUri(self::CHANGE_DECK_URI, ['id' => (string) $room->getId()]), ['deck' => '/api/decks/'.$deck->getId()]);

        self::assertResponseIsSuccessful();
    }

    public function testChangeDeckAsOwner()
    {
        $room = ThereIs::aRoom()->withOwner($this->currentUser)->build();

        $deck = $this->currentUser->getDecks()[0];

        $this->post($this->getUri(self::CHANGE_DECK_URI, ['id' => (string) $room->getId()]), ['deck' => '/api/decks/'.$deck->getId()]);

        self::assertSame($deck, $room->getOwnerDeck());
    }

    public function testChangeDeckAsOpponent()
    {
        $owner = $this->createUser('opponent');
        $room = ThereIs::aRoom()
            ->withOwner($owner)
            ->withOpponent($this->currentUser)
            ->build();

        $deck = $this->currentUser->getDecks()[0];

        $this->post($this->getUri(self::CHANGE_DECK_URI, ['id' => (string) $room->getId()]), ['deck' => '/api/decks/'.$deck->getId()]);

        self::assertSame($deck, $room->getOpponentDeck());
    }

    public function testChangeDeckForbidden()
    {
        $room = ThereIs::aRoom()->build();

        $deck = $this->currentUser->getDecks()[0];

        $this->post($this->getUri(self::CHANGE_DECK_URI, ['id' => (string) $room->getId()]), ['deck' => '/api/decks/'.$deck->getId()]);

        self::assertResponseStatusCodeSame(403);
    }
}
