<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;


use App\Repository\DeckRepository;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource]
#[ORM\Entity(repositoryClass: DeckRepository::class)]
class Deck
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne(inversedBy: 'decks')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 255)]
    private string $characterCard;

    #[ORM\Column]
    private array $cards = [];

    public function __construct(User $user, string $name, string $characterCard, array $cards = [])
    {
        $this->user = $user;
        $this->name = $name;
        $this->characterCard = $characterCard;
        $this->cards = $cards;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCharacterCard(): string
    {
        return $this->characterCard;
    }

    public function setCharacterCard(string $characterCard): static
    {
        $this->characterCard = $characterCard;

        return $this;
    }

    public function getCards(): array
    {
        return $this->cards;
    }

    public function setCards(array $cards): static
    {
        $this->cards = $cards;

        return $this;
    }
}
