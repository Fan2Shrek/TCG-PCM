<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\Inventory\CardInventory;
use App\Entity\Inventory\Inventory;
use App\Entity\Trade;
use App\Entity\User;
use App\Enum\TradeStatusEnum;
use App\Service\TradeExecutor;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class TradeExecutorTest extends TestCase
{
    public function testExecuteSwapsTheOfferedCards(): void
    {
        [$trade, $initiatorInventory, $recipientInventory] = $this->aTradeWithOffers(initiatorCardQuantity: 55, recipientCardQuantity: 55);

        $executor = new TradeExecutor($this->fakeEntityManager());
        $executor->execute($trade);

        self::assertSame(TradeStatusEnum::COMPLETED, $trade->getStatus());
        self::assertNotNull($trade->getCompletedAt());
        self::assertSame(54, $initiatorInventory->findCardByCardId('card_a')->getQuantity());
        self::assertSame(1, $initiatorInventory->findCardByCardId('card_b')->getQuantity());
        self::assertSame(54, $recipientInventory->findCardByCardId('card_b')->getQuantity());
        self::assertSame(1, $recipientInventory->findCardByCardId('card_a')->getQuantity());
    }

    public function testExecuteRejectsWhenBelowFiftyTotalCards(): void
    {
        [$trade, $initiatorInventory] = $this->aTradeWithOffers(initiatorCardQuantity: 40, recipientCardQuantity: 55);

        $executor = new TradeExecutor($this->fakeEntityManager());

        try {
            $executor->execute($trade);
            self::fail('Expected an HttpException to be thrown.');
        } catch (HttpException $exception) {
            self::assertSame(400, $exception->getStatusCode());
        }

        // Nothing was flushed, so the DB is untouched even though the in-memory quantity was
        // mutated before the total-cards check failed; the request ends right after this throw.
        self::assertSame(TradeStatusEnum::ACTIVE, $trade->getStatus());
        self::assertSame(39, $initiatorInventory->findCardByCardId('card_a')->getQuantity());
    }

    public function testExecuteRejectsWhenCardNoLongerOwned(): void
    {
        [$trade, , , $initiatorGivenCard] = $this->aTradeWithOffers(initiatorCardQuantity: 55, recipientCardQuantity: 55);
        $initiatorGivenCard->setQuantity(0);

        $executor = new TradeExecutor($this->fakeEntityManager());

        try {
            $executor->execute($trade);
            self::fail('Expected an HttpException to be thrown.');
        } catch (HttpException $exception) {
            self::assertSame(400, $exception->getStatusCode());
        }

        self::assertSame(TradeStatusEnum::ACTIVE, $trade->getStatus());
    }

    /**
     * @return array{Trade, Inventory, Inventory, CardInventory}
     */
    private function aTradeWithOffers(int $initiatorCardQuantity, int $recipientCardQuantity): array
    {
        $initiator = new User('initiator', 'initiator@test.local');
        $recipient = new User('recipient', 'recipient@test.local');
        $this->setId($initiator, 1);
        $this->setId($recipient, 2);

        $initiatorInventory = new Inventory($initiator);
        $recipientInventory = new Inventory($recipient);
        $initiator->setInventory($initiatorInventory);
        $recipient->setInventory($recipientInventory);

        $initiatorGivenCard = new CardInventory($initiatorInventory, 'card_a');
        $initiatorGivenCard->setQuantity($initiatorCardQuantity);
        $initiatorInventory->addCard($initiatorGivenCard);

        $recipientGivenCard = new CardInventory($recipientInventory, 'card_b');
        $recipientGivenCard->setQuantity($recipientCardQuantity);
        $recipientInventory->addCard($recipientGivenCard);

        $trade = new Trade($initiator, $recipient);
        $trade->setInitiatorCard('card_a');
        $trade->setRecipientCard('card_b');
        $trade->setInitiatorConfirmed(true);
        $trade->setRecipientConfirmed(true);

        return [$trade, $initiatorInventory, $recipientInventory, $initiatorGivenCard];
    }

    private function setId(User $user, int $id): void
    {
        $property = new \ReflectionProperty(User::class, 'id');
        $property->setValue($user, $id);
    }

    private function fakeEntityManager(): EntityManagerInterface
    {
        $em = $this->createStub(EntityManagerInterface::class);
        $em->method('wrapInTransaction')->willReturnCallback(static fn(callable $func) => $func());
        $em->method('lock')->willReturnCallback(static function (): void {});
        $em->method('persist')->willReturnCallback(static function (): void {});
        $em->method('flush')->willReturnCallback(static function (): void {});

        return $em;
    }
}
