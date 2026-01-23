<?php

declare(strict_types=1);

namespace App\Tests\Resources;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;

final class DoctrineCollector
{
    private bool $enabled = false;
    private bool $isBooted = false;

    private array $insertedEntities = [];
    private ?object $lastInsertedEntity = null;

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        $this->insertedEntities[] = $args->getObject();
        $this->lastInsertedEntity = $args->getObject();
    }

    public function enable(): void
    {
        $this->enabled = true;
        $this->insertedEntities = [];
    }

    public function getAllInsertedEntities(): array
    {
        return $this->insertedEntities;
    }

    public function getInsertedEntity(): ?object
    {
        return $this->lastInsertedEntity;
    }

    public function disable(): void
    {
        $this->enabled = false;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function start(): void
    {
        $this->boot();
        $this->enable();
    }

    private function boot(): void
    {
        if ($this->isBooted) {
            return;
        }

        $this->isBooted = true;

        $this->entityManager
            ->getEventManager()
            ->addEventListener(['postPersist'], $this)
        ;
    }
}
