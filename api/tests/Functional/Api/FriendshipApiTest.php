<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use App\Entity\Friendship;
use App\Tests\Functional\EntityAssertionTrait;
use App\Tests\Functional\FunctionalTestCase;
use App\Tests\Resources\Fixtures\ThereIs;

final class FriendshipApiTest extends FunctionalTestCase
{
    use EntityAssertionTrait;

    protected const string SEND_URI = '/api/friendships';
    protected const string ACCEPT_URI = '/api/friendships/{id}/accept';
    protected const string DECLINE_URI = '/api/friendships/{id}/decline';
    protected const string CANCEL_URI = '/api/friendships/{id}/cancel';
    protected const string REMOVE_URI = '/api/friendships/{id}/remove';
    protected const string LIST_URI = '/api/friendships';
    protected const string PENDING_URI = '/api/pending-friend-requests';

    public function testSendFriendRequestSuccess()
    {
        $other = ThereIs::anUser()->build();

        $this->post(self::SEND_URI, ['username' => $other->getUsername()]);

        self::assertResponseIsSuccessful();
        self::assertEntityCount(1, Friendship::class);
    }

    public function testSendFriendRequestToSelfFails()
    {
        $this->post(self::SEND_URI, ['username' => $this->currentUser->getUsername()]);

        self::assertResponseStatusCodeSame(400);
    }

    public function testSendDuplicateFriendRequestFails()
    {
        $other = ThereIs::anUser()->build();
        ThereIs::aFriendship()->withRequester($this->currentUser)->withAddressee($other)->build();

        $this->post(self::SEND_URI, ['username' => $other->getUsername()]);

        self::assertResponseStatusCodeSame(400);
        self::assertEntityCount(1, Friendship::class);
    }

    public function testSendFriendRequestToUnknownUserFails()
    {
        $this->post(self::SEND_URI, ['username' => 'does-not-exist']);

        self::assertResponseStatusCodeSame(404);
    }

    public function testAcceptFriendRequestSuccess()
    {
        $requester = ThereIs::anUser()->build();
        $friendship = ThereIs::aFriendship()->withRequester($requester)->withAddressee($this->currentUser)->build();

        $this->post($this->getUri(self::ACCEPT_URI, ['id' => (string) $friendship->getId()]));

        self::assertResponseIsSuccessful();
        self::assertSame(\App\Enum\FriendshipStatusEnum::ACCEPTED, $friendship->getStatus());
    }

    public function testAcceptFriendRequestForbiddenForNonAddressee()
    {
        $friendship = ThereIs::aFriendship()->build();

        $this->post($this->getUri(self::ACCEPT_URI, ['id' => (string) $friendship->getId()]));

        self::assertResponseStatusCodeSame(403);
    }

    public function testDeclineFriendRequestSuccess()
    {
        $requester = ThereIs::anUser()->build();
        $friendship = ThereIs::aFriendship()->withRequester($requester)->withAddressee($this->currentUser)->build();

        $this->post($this->getUri(self::DECLINE_URI, ['id' => (string) $friendship->getId()]));

        self::assertResponseIsSuccessful();
        self::assertEntityCount(0, Friendship::class);
    }

    public function testCancelFriendRequestSuccess()
    {
        $addressee = ThereIs::anUser()->build();
        $friendship = ThereIs::aFriendship()->withRequester($this->currentUser)->withAddressee($addressee)->build();

        $this->post($this->getUri(self::CANCEL_URI, ['id' => (string) $friendship->getId()]));

        self::assertResponseIsSuccessful();
        self::assertEntityCount(0, Friendship::class);
    }

    public function testCancelFriendRequestForbiddenForNonRequester()
    {
        $friendship = ThereIs::aFriendship()->withAddressee($this->currentUser)->build();

        $this->post($this->getUri(self::CANCEL_URI, ['id' => (string) $friendship->getId()]));

        self::assertResponseStatusCodeSame(403);
    }

    public function testRemoveFriendSuccess()
    {
        $other = ThereIs::anUser()->build();
        $friendship = ThereIs::aFriendship()->withRequester($this->currentUser)->withAddressee($other)->accepted()->build();

        $this->post($this->getUri(self::REMOVE_URI, ['id' => (string) $friendship->getId()]));

        self::assertResponseIsSuccessful();
        self::assertEntityCount(0, Friendship::class);
    }

    public function testRemoveFriendFailsWhenNotAccepted()
    {
        $friendship = ThereIs::aFriendship()->withRequester($this->currentUser)->build();

        $this->post($this->getUri(self::REMOVE_URI, ['id' => (string) $friendship->getId()]));

        self::assertResponseStatusCodeSame(400);
    }

    public function testListFriendshipsOnlyReturnsAccepted()
    {
        $other1 = ThereIs::anUser()->build();
        $other2 = ThereIs::anUser()->build();
        ThereIs::aFriendship()->withRequester($this->currentUser)->withAddressee($other1)->accepted()->build();
        ThereIs::aFriendship()->withRequester($this->currentUser)->withAddressee($other2)->build();

        $r = $this->get(self::LIST_URI);
        $content = $r->toArray();

        self::assertCount(1, $content);
    }

    public function testListPendingReturnsIncomingRequests()
    {
        $requester = ThereIs::anUser()->build();
        ThereIs::aFriendship()->withRequester($requester)->withAddressee($this->currentUser)->build();

        $r = $this->get(self::PENDING_URI);
        $content = $r->toArray();

        self::assertCount(1, $content);
    }
}
