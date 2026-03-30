<?php

declare(strict_types=1);

namespace App\Tests\Resources\Fixtures\Builder\Inventory;

use App\Entity\Inventory\CardInventory;
use App\Entity\Inventory\Inventory;
use App\Tests\Resources\Fixtures\Builder\AbstractBuilder;

/*
 * @extends AbstractBuilder<Inventory>
 */
final class CardInventoryBuilder extends AbstractBuilder
{
    private Inventory $inventory;
    private string $card;
    private int $quantity = 1;

    public function doBuild(): void
    {
        $this->entity = new CardInventory($this->inventory, $this->card);
        $this->entity->setQuantity($this->quantity);
    }

    public function inInventory(Inventory $inventory): self
    {
        $this->inventory = $inventory;

        return $this;
    }

    public function withCard(string $card): self
    {
        $this->card = $card;

        return $this;
    }

    public function withQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }
}
