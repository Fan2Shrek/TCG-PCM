<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Inventory\CardInventory;
use App\Repository\Inventory\CardInventoryRepository;
use App\Repository\Inventory\InventoryRepository;
use App\Service\Game\CardRegistryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'app:inventory:add')]
final class GiveCardCommand
{
    public function __construct(
        private CardRegistryInterface $cardRegistry,
        private InventoryRepository $inventoryRepository,
        private EntityManagerInterface $entityManager,
    ) {}

    public function __invoke(#[Argument('userId')] string $userId, #[Argument('cardId')] ?string $cardId = null, #[Argument('quantity')] int $quantity = 1): int
    {
        /** @var \App\Entity\Inventory\Inventory $inv */
        $inv = $this->inventoryRepository->find($userId);

        if (!$inv) {
            throw new \RuntimeException(\sprintf('Inventory for user "%s" not found.', $userId));
        }

        $cards = $cardId ? [$cardId] : $inv->getCards();

        foreach ($this->cardRegistry->getAllBy([]) as $card) {
            $result = $cards->filter(fn(CardInventory $c) => $c->getCard() === $card);
            /** @var CardInventory|null $cardInv */
            $cardInv = $result->isEmpty() ? null : $result->first();

            if (!$cardInv) {
                $cardInv = new CardInventory($inv, $card);
                $this->entityManager->persist($cardInv);
            }

            $cardInv->setQuantity($cardInv->getQuantity() + $quantity);
        }

        $this->entityManager->flush();

        return 0;
    }
}
