<?php

declare(strict_types=1);

namespace App\Api\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Api\DTO\InventoryDTO;
use App\Entity\Inventory\CardInventory;
use App\Entity\Inventory\Inventory;
use App\Service\Auth\CurrentUserProviderInterface;
use App\Service\Game\CardFactoryInterface;

/**
 * @implements ProviderInterface<Inventory>
 */
final class InventoryProvider implements ProviderInterface
{
    public function __construct(
        private CurrentUserProviderInterface $currentUserProvider,
        private CardFactoryInterface $cardFactory,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): InventoryDTO
    {
        $inventory = $this->currentUserProvider->getCurrentUser()->getInventory();

        return new InventoryDTO(array_map(fn(CardInventory $c) => [
            'card' => $this->cardFactory->create($c->getCard()),
            'quantity' => $c->getQuantity(),
        ], $inventory->getCards()->toArray()));
    }
}
