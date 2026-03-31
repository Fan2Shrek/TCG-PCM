<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Inventory\CardInventory;
use App\Entity\Inventory\Inventory;
use App\Game\AbstractCard;
use App\Service\Auth\CurrentUserProviderInterface;
use Doctrine\ORM\EntityManagerInterface;

final class InventoryUpdater
{
    public function __construct(
        private CurrentUserProviderInterface $currentUserProvider,
        private EntityManagerInterface $em,
    ) {}

    /**
     * @param AbstractCard[] $cards
     */
    public function addCards(array $cards): void
    {
        foreach ($cards as $card) {
            $this->addCard($card);
        }

        $this->em->flush();
    }

    private function addCard(AbstractCard $card): void
    {
        $cardId = $card->getId();
        $inventory = $this->getCurrentUserInventory();
        $cardInventory = $inventory->findCardByCardId($cardId);

        if (!$cardInventory) {
            $cardInventory = new CardInventory($inventory, $cardId);
            $inventory->addCard($cardInventory);

            $this->em->persist($cardInventory);
        }

        $cardInventory->incrementQuantity();
    }

    private function getCurrentUserInventory(): Inventory
    {
        return $this->currentUserProvider->getCurrentUser()->getInventory();
    }
}
