<?php

declare(strict_types=1);

namespace App\DataFixtures\Inventory;

use App\DataFixtures\AbstractFixtures;
use App\Entity\Inventory\CardInventory;
use App\Entity\Inventory\Inventory;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

final class CardInventoryFixtures extends AbstractFixtures implements DependentFixtureInterface
{
    public function __construct()
    {
        return parent::__construct(CardInventory::class);
    }

    public function getData(): iterable
    {
        yield [
            'inventory' => $this->getReference('Inventory_1', Inventory::class),
            'card' => 'Spicy-D6',
            'quantity' => 6,
        ];

        yield [
            'inventory' => $this->getReference('Inventory_1', Inventory::class),
            'card' => 'Redbloons',
            'quantity' => 60,
        ];

        yield [
            'inventory' => $this->getReference('Inventory_1', Inventory::class),
            'card' => 'Gitman',
            'quantity' => 2,
        ];

        yield [
            'inventory' => $this->getReference('Inventory_1', Inventory::class),
            'card' => 'Pierrot',
            'quantity' => 40,
        ];

        yield [
            'inventory' => $this->getReference('Inventory_2', Inventory::class),
            'card' => 'Stonks',
            'quantity' => 1,
        ];

        yield [
            'inventory' => $this->getReference('Inventory_2', Inventory::class),
            'card' => 'D6',
            'quantity' => 100,
        ];

        yield [
            'inventory' => $this->getReference('Inventory_2', Inventory::class),
            'card' => 'HackedZone',
            'quantity' => 1,
        ];
    }

    public function getDependencies(): array
    {
        return [
            InventoryFixtures::class,
        ];
    }
}
