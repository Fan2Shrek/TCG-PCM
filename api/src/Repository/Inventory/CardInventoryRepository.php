<?php

declare(strict_types=1);

namespace App\Repository\Inventory;

use App\Entity\Inventory\CardInventory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CardInventory>
 */
final class CardInventoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        return parent::__construct($registry, CardInventory::class);
    }
}
