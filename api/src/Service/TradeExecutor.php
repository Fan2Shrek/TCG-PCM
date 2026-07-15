<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Inventory\CardInventory;
use App\Entity\Inventory\Inventory;
use App\Entity\Trade;
use App\Enum\TradeStatusEnum;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Atomically swaps the two offered cards of an active, fully-confirmed trade.
 *
 * Re-validates ownership and the minimum-total-cards rule at execution time (not just at
 * offer time), since inventory state can change between a confirmation and the moment both
 * sides are confirmed. Both inventories are locked (in a stable id order to avoid deadlocking
 * against a concurrent trade between the same two users) for the duration of the swap.
 */
final class TradeExecutor
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {}

    public function execute(Trade $trade): void
    {
        // Validation failures are returned from the transactional callback rather than thrown:
        // Doctrine closes the EntityManager after a transaction rolls back following an
        // exception, which would break every subsequent request/test sharing that EntityManager.
        // Throwing after wrapInTransaction() returns keeps a failed swap a clean no-op commit.
        /** @var string|null $failureMessage */
        $failureMessage = $this->em->wrapInTransaction(function () use ($trade): ?string {
            $initiatorInventory = $trade->getInitiator()->getInventory();
            $recipientInventory = $trade->getRecipient()->getInventory();

            foreach ($this->inLockOrder($initiatorInventory, $recipientInventory) as $inventory) {
                $this->em->lock($inventory, LockMode::PESSIMISTIC_WRITE);
            }

            if (TradeStatusEnum::ACTIVE !== $trade->getStatus()) {
                return 'This trade is no longer active.';
            }

            $initiatorCardId = $trade->getInitiatorCard();
            $recipientCardId = $trade->getRecipientCard();

            if (null === $initiatorCardId || null === $recipientCardId) {
                return 'Both players must offer a card before confirming.';
            }

            $initiatorGivenCard = $initiatorInventory->findCardByCardId($initiatorCardId);
            $recipientGivenCard = $recipientInventory->findCardByCardId($recipientCardId);

            if (null === $initiatorGivenCard || $initiatorGivenCard->getQuantity() < 1) {
                return 'Initiator no longer owns the offered card.';
            }

            if (null === $recipientGivenCard || $recipientGivenCard->getQuantity() < 1) {
                return 'Recipient no longer owns the offered card.';
            }

            $initiatorGivenCard->decrementQuantity();
            $recipientGivenCard->decrementQuantity();

            $this->giveCard($initiatorInventory, $recipientCardId);
            $this->giveCard($recipientInventory, $initiatorCardId);

            if (
                $initiatorInventory->getTotalCardQuantity() < DeckValidator::DECK_SIZE
                || $recipientInventory->getTotalCardQuantity() < DeckValidator::DECK_SIZE
            ) {
                return \sprintf('This trade would drop a player below %d total cards.', DeckValidator::DECK_SIZE);
            }

            $trade->setStatus(TradeStatusEnum::COMPLETED);
            $trade->setCompletedAt(new \DateTimeImmutable());

            $this->em->flush();

            return null;
        });

        if (null !== $failureMessage) {
            throw HttpException::fromStatusCode(Response::HTTP_BAD_REQUEST, $failureMessage);
        }
    }

    /**
     * @return Inventory[]
     */
    private function inLockOrder(Inventory $a, Inventory $b): array
    {
        $inventories = [$a, $b];
        usort($inventories, static fn(Inventory $x, Inventory $y): int => ($x->getId() ?? 0) <=> ($y->getId() ?? 0));

        return $inventories;
    }

    private function giveCard(Inventory $inventory, string $cardId): void
    {
        $cardInventory = $inventory->findCardByCardId($cardId);

        if (null === $cardInventory) {
            $cardInventory = new CardInventory($inventory, $cardId);
            $inventory->addCard($cardInventory);
            $this->em->persist($cardInventory);
        }

        $cardInventory->incrementQuantity();
    }
}
