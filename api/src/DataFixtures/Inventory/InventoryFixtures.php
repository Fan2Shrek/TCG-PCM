<?php

declare(strict_types=1);

namespace App\DataFixtures\Inventory;

use App\DataFixtures\AbstractFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Inventory\Inventory;
use App\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

final class InventoryFixtures extends AbstractFixtures implements DependentFixtureInterface
{
    public function __construct()
    {
        parent::__construct(Inventory::class);
    }

    public function getData(): iterable
    {
        yield [
            'user' => $this->getReference('User_1', User::class),
        ];

        yield [
            'user' => $this->getReference('User_2', User::class),
        ];
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
