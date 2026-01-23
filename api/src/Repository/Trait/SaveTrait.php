<?php

declare(strict_types=1);

namespace App\Repository\Trait;

/** @template T of object */
trait SaveTrait
{
    /**
     * @param T $entity
     */
    public function save(object $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
