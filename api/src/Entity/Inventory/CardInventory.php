<?php

declare(strict_types=1);

namespace App\Entity\Inventory;

use App\Repository\Inventory\CardInventoryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CardInventoryRepository::class)]
final class CardInventory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne(inversedBy: 'cards')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Inventory $inventory;

    #[ORM\Column(length: 255)]
    private string $card;

    #[ORM\Column(options: ['default' => 0])]
    private int $quantity = 0;

    public function __construct(Inventory $inventory, string $card)
    {
        $this->inventory = $inventory;
        $this->card = $card;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getInventory(): Inventory
    {
        return $this->inventory;
    }

    public function getCard(): string
    {
        return $this->card;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function incrementQuantity(): static
    {
        ++$this->quantity;

        return $this;
    }

    public function decrementQuantity(): static
    {
        if ($this->quantity > 0) {
            --$this->quantity;
        }

        return $this;
    }
}
