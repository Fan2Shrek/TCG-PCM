<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Api\DTO\CreateDeckInput;
use App\Api\Processor\CreateDeckProcessor;
use App\Api\Processor\DeleteDeckProcessor;
use App\Api\Provider\UserDecksProvider;
use App\Repository\DeckRepository;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(operations: [
    new Post(uriTemplate: '/decks', input: CreateDeckInput::class, processor: CreateDeckProcessor::class, status: 201),
    new GetCollection(uriTemplate: '/decks', provider: UserDecksProvider::class),
    new Get(uriTemplate: '/decks/{id}', security: 'object.getUser() == user'),
    new Patch(uriTemplate: '/decks/{id}', security: 'object.getUser() == user'),
    new Delete(uriTemplate: '/decks/{id}', security: 'object.getUser() == user', processor: DeleteDeckProcessor::class, status: 204),
])]
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

    /**
     * @var string[] $cards
     */
    #[ORM\Column]
    private array $cards = [];

    #[ORM\Column(options: ['default' => false])]
    private bool $isFavorite = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $isDeleted = false;

    /**
     * @param string[] $cards
     */
    public function __construct(User $user, string $name, string $characterCard, array $cards = [])
    {
        $this->user = $user;
        $this->name = $name;
        $this->characterCard = $characterCard;
        $this->cards = $cards;
        $this->isFavorite = false;
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

    /**
     * @return string[]
     */
    public function getCards(): array
    {
        return $this->cards;
    }

    /**
     * @param string[] $cards
     */
    public function setCards(array $cards): static
    {
        $this->cards = $cards;

        return $this;
    }

    public function getIsFavorite(): bool
    {
        return $this->isFavorite;
    }

    public function setFavorite(bool $isFavorite): static
    {
        $this->isFavorite = $isFavorite;

        return $this;
    }

    public function setIsFavorite(bool $isFavorite): static
    {
        return $this->setFavorite($isFavorite);
    }

    public function isDeleted(): bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(bool $isDeleted): static
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }
}
