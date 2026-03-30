<?php

declare(strict_types=1);

namespace App\Api\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Inventory\Inventory;
use App\Service\Auth\CurrentUserProviderInterface;

/**
 * @implements ProviderInterface<Inventory>
 */
final class InventoryProvider implements ProviderInterface
{
    public function __construct(
        private CurrentUserProviderInterface $currentUserProvider,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Inventory
    {
        return $this->currentUserProvider->getCurrentUser()->getInventory();
    }
}
