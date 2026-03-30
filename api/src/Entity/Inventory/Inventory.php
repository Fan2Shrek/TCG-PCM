<?php

namespace App\Entity\Inventory;

use App\Entity\User;
use App\Repository\Inventory\InventoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InventoryRepository::class)]
class Inventory
{
    #[ORM\Id]
    #[ORM\Column]
    private int $id;

    #[ORM\OneToOne(inversedBy: 'inventory', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private User $owner;

    /**
     * @var Collection<int, CardInventory>
     */
    #[ORM\OneToMany(targetEntity: CardInventory::class, mappedBy: 'inventory', orphanRemoval: true)]
    private Collection $cards;

    public function __construct(User $user)
    {
        $this->id = $user->getId();
        $this->owner = $user;
        $this->cards = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): User
    {
        return $this->owner;
    }

    /**
     * @return Collection<int, CardInventory>
     */
    public function getCards(): Collection
    {
        return $this->cards;
    }

    public function addCard(CardInventory $card): static
    {
        if (!$this->cards->contains($card)) {
            $this->cards->add($card);
        }

        return $this;
    }

    public function removeCard(CardInventory $card): static
    {
        $this->cards->removeElement($card);

        return $this;
    }
}
