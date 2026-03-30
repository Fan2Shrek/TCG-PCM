<?php

declare(strict_types=1);

namespace App\Tests\Resources\Fixtures\Builder\Inventory;

use App\Entity\Inventory\Inventory;
use App\Entity\User;
use App\Tests\Resources\Fixtures\Builder\AbstractBuilder;
use App\Tests\Resources\Fixtures\ThereIs;

/*
 * @extends AbstractBuilder<Inventory>
 */
final class InventoryBuilder extends AbstractBuilder
{
    private User $owner;
    private array $cards = [];

    public function build(): object
    {
        if (null === ($this->owner ?? null)) {
            $this->owner = ThereIs::anUser()->build();
        }

        try {
            $this->entity = $this->owner->getInventory();
        } catch (\Throwable) {
            $this->entity = new Inventory($this->owner);
        }

        $this->getEm()->persist($this->entity);
        $this->getEm()->flush();

        $this->doBuild();

        return $this->entity;
    }

    public function for(User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function withCard(string $card = 'card', int $quantity = 1): self
    {
        $this->cards[] = ThereIs::aCardInventory()->withCard($card)->withQuantity($quantity);

        return $this;
    }

    protected function doBuild(): void
    {
        foreach ($this->cards as $builder) {
            $card = $builder->inInventory($this->entity)->build();
            $this->entity->addCard($card);
        }
    }
}
