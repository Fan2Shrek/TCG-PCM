<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use App\Entity\Trade;
use App\Entity\User;
use App\Enum\TradeStatusEnum;
use App\Tests\Functional\EntityAssertionTrait;
use App\Tests\Functional\FunctionalTestCase;
use App\Tests\Resources\Fixtures\ThereIs;

final class TradeApiTest extends FunctionalTestCase
{
    use EntityAssertionTrait;

    protected const string CREATE_URI = '/api/trades';
    protected const string OFFER_URI = '/api/trades/{id}/offer';
    protected const string CONFIRM_URI = '/api/trades/{id}/confirm';
    protected const string CANCEL_URI = '/api/trades/{id}/cancel';

    protected function createUser(?string $username = null, ?string $password = null, bool $withSubEntities = false): User
    {
        $user = parent::createUser($username, $password, $withSubEntities);
        $user->setInventory(ThereIs::anInventory()->for($user)->build());
        $this->getEm()->flush();

        return $user;
    }

    private function aFriendOf(User $user): User
    {
        $friend = ThereIs::anUser()->build();
        ThereIs::aFriendship()->withRequester($user)->withAddressee($friend)->accepted()->build();

        return $friend;
    }

    public function testCreateTradeSuccess()
    {
        $friend = $this->aFriendOf($this->currentUser);

        $this->post(self::CREATE_URI, ['friendId' => $friend->getId()]);

        self::assertResponseIsSuccessful();
        self::assertEntityCount(1, Trade::class);
    }

    public function testCreateTradeRequiresFriendship()
    {
        $stranger = ThereIs::anUser()->build();

        $this->post(self::CREATE_URI, ['friendId' => $stranger->getId()]);

        self::assertResponseStatusCodeSame(403);
    }

    public function testCreateTradeFailsIfAlreadyActive()
    {
        $friend = $this->aFriendOf($this->currentUser);
        ThereIs::aTrade()->withInitiator($this->currentUser)->withRecipient(ThereIs::anUser()->build())->build();

        $this->post(self::CREATE_URI, ['friendId' => $friend->getId()]);

        self::assertResponseStatusCodeSame(400);
    }

    public function testOfferCardSuccess()
    {
        $friend = $this->aFriendOf($this->currentUser);
        ThereIs::anInventory()->for($this->currentUser)->withCard('card_a', 5)->build();
        $trade = ThereIs::aTrade()->withInitiator($this->currentUser)->withRecipient($friend)->build();

        $this->post($this->getUri(self::OFFER_URI, ['id' => (string) $trade->getId()]), ['card' => 'card_a']);

        self::assertResponseIsSuccessful();
        self::assertSame('card_a', $trade->getInitiatorCard());
    }

    public function testOfferCardFailsIfNotOwned()
    {
        $friend = $this->aFriendOf($this->currentUser);
        $trade = ThereIs::aTrade()->withInitiator($this->currentUser)->withRecipient($friend)->build();

        $this->post($this->getUri(self::OFFER_URI, ['id' => (string) $trade->getId()]), ['card' => 'card_a']);

        self::assertResponseStatusCodeSame(400);
    }

    public function testOfferCardResetsBothConfirmations()
    {
        $friend = $this->aFriendOf($this->currentUser);
        ThereIs::anInventory()->for($this->currentUser)->withCard('card_a', 5)->build();
        $trade = ThereIs::aTrade()
            ->withInitiator($this->currentUser)
            ->withRecipient($friend)
            ->withInitiatorCard('card_old')
            ->withRecipientCard('card_b')
            ->withInitiatorConfirmed()
            ->withRecipientConfirmed()
            ->build();

        $this->post($this->getUri(self::OFFER_URI, ['id' => (string) $trade->getId()]), ['card' => 'card_a']);

        self::assertResponseIsSuccessful();
        self::assertFalse($trade->isInitiatorConfirmed());
        self::assertFalse($trade->isRecipientConfirmed());
    }

    public function testConfirmFailsWithoutOwnOffer()
    {
        $friend = $this->aFriendOf($this->currentUser);
        $trade = ThereIs::aTrade()->withInitiator($this->currentUser)->withRecipient($friend)->build();

        $this->post($this->getUri(self::CONFIRM_URI, ['id' => (string) $trade->getId()]));

        self::assertResponseStatusCodeSame(400);
    }

    public function testConfirmWaitsForOtherSide()
    {
        $friend = $this->aFriendOf($this->currentUser);
        ThereIs::anInventory()->for($this->currentUser)->withCard('card_a', 5)->build();
        $trade = ThereIs::aTrade()
            ->withInitiator($this->currentUser)
            ->withRecipient($friend)
            ->withInitiatorCard('card_a')
            ->withRecipientCard('card_b')
            ->build();

        $this->post($this->getUri(self::CONFIRM_URI, ['id' => (string) $trade->getId()]));

        self::assertResponseIsSuccessful();
        self::assertTrue($trade->isInitiatorConfirmed());
        self::assertFalse($trade->isRecipientConfirmed());
        self::assertSame(TradeStatusEnum::ACTIVE, $trade->getStatus());
    }

    public function testConfirmForbiddenForOutsider()
    {
        $trade = ThereIs::aTrade()->build();

        $this->post($this->getUri(self::CONFIRM_URI, ['id' => (string) $trade->getId()]));

        self::assertResponseStatusCodeSame(403);
    }

    public function testCancelTradeSuccess()
    {
        $friend = $this->aFriendOf($this->currentUser);
        $trade = ThereIs::aTrade()->withInitiator($this->currentUser)->withRecipient($friend)->build();

        $this->post($this->getUri(self::CANCEL_URI, ['id' => (string) $trade->getId()]));

        self::assertResponseIsSuccessful();
        self::assertSame(TradeStatusEnum::CANCELLED, $trade->getStatus());
    }

    public function testCancelTradeForbiddenForOutsider()
    {
        $trade = ThereIs::aTrade()->build();

        $this->post($this->getUri(self::CANCEL_URI, ['id' => (string) $trade->getId()]));

        self::assertResponseStatusCodeSame(403);
    }
}
