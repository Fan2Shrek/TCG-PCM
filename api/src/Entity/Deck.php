<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Game\AbstractCard;
use App\Game\Card\Character\AbstractCharacterCard;
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

    /**
     * @var class-string<AbstractCharacterCard>
     */
    #[ORM\Column(length: 255)]
    private string $characterCard;

    /**
     * @var array<class-string<AbstractCard>>
     */
    #[ORM\Column]
    private array $cards = [];

    /**
     * @param class-string<AbstractCharacterCard> $characterCard
     * @param array<class-string<AbstractCard>> $cards
     */
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

    /**
     * @return class-string<AbstractCharacterCard>
     */
    public function getCharacterCard(): string
    {
        return $this->characterCard;
    }

    /**
     * @param class-string<AbstractCharacterCard> $characterCard
     */
    public function setCharacterCard(string $characterCard): static
    {
        if (!class_exists($characterCard) || !is_subclass_of($characterCard, AbstractCharacterCard::class)) {
            throw new \InvalidArgumentException('Invalid character card class: '.$characterCard);
        }

        $this->characterCard = $characterCard;

        return $this;
    }

    /**
     * @return array<class-string<AbstractCard>>
     */
    public function getCards(): array
    {
        return $this->cards;
    }

    /**
     * @param array<class-string<AbstractCard>> $cards
     */
    public function setCards(array $cards): static
    {
        $this->cards = $cards;

        return $this;
    }
}
