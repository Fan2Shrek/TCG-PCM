<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Tests\Resources\DoctrineCollector;
use PHPUnit\Framework\Attributes\BeforeClass;

trait EntityAssertionTrait
{
    private static DoctrineCollector $collector;

    #[BeforeClass]
    public static function startCollector(): void
    {
        self::$collector = static::getContainer()
            ->get(DoctrineCollector::class)
        ;

        self::$collector->start();
    }

    protected static function assertEntityCount(int $expectedCount, string $entityClass): void
    {
        self::assertSame(
            $expectedCount,
            static::getEm()
                ->getRepository($entityClass)
                ->count([]),
        );
    }

    protected function getLastInsertedEntity(): object
    {
        return self::$collector->getInsertedEntity();
    }
}
