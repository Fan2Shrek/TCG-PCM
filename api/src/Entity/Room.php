<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Api\Provider\GameProvider;
use App\Api\Provider\WaitingRoomProvider;
use App\Domain\Command\Game\PlayGameCommand;
use App\Domain\Command\Room\ChangeDeckCommand;
use App\Domain\Command\Room\CreateRoomCommand;
use App\Domain\Command\Room\JoinRoomCommand;
use App\Domain\Command\Room\StartRoomCommand;
use App\Enum\RoomStatusEnum;
use App\Repository\RoomRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ApiResource(operations: [
    new Get(uriTemplate: '/game/{id}', provider: GameProvider::class),
    new GetCollection(uriTemplate: '/waiting-rooms', provider: WaitingRoomProvider::class, normalizationContext: ['groups' => 'api:room:list']),

    new Post(
        uriTemplate: '/rooms/create',
        messenger: 'input',
        normalizationContext: ['groups' => 'api:room:create'],
        input: CreateRoomCommand::class,
        condition: "is_enable('create_room')",
        status: 201,
    ),
    new Post(uriTemplate: '/rooms/{id}/join', messenger: 'input', input: JoinRoomCommand::class),
    new Post(uriTemplate: '/rooms/{id}/start', messenger: 'input', input: StartRoomCommand::class, status: 204),
    new Post(uriTemplate: '/rooms/{id}/change_deck', messenger: 'input', input: ChangeDeckCommand::class, status: 204),
    new Post(uriTemplate: '/game/{id}/play', messenger: 'input', input: PlayGameCommand::class, status: 200),
])]
#[ORM\Entity(repositoryClass: RoomRepository::class)]
class Room
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private User $owner;

    #[ORM\ManyToOne]
    private ?User $opponent = null;

    #[ORM\Column(enumType: RoomStatusEnum::class)]
    private RoomStatusEnum $status;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private Deck $ownerDeck;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Deck $opponentDeck = null;

    #[ORM\Column(nullable: true)]
    private ?string $winnerId = null;

    public function __construct(User $owner)
    {
        $this->owner = $owner;
        $this->status = RoomStatusEnum::WAITING;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getOwner(): User
    {
        return $this->owner;
    }

    public function getOpponent(): ?User
    {
        return $this->opponent;
    }

    public function setOpponent(?User $opponent): static
    {
        $this->opponent = $opponent;

        return $this;
    }

    public function getStatus(): RoomStatusEnum
    {
        return $this->status;
    }

    public function setStatus(RoomStatusEnum $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getOwnerDeck(): Deck
    {
        return $this->ownerDeck;
    }

    public function setOwnerDeck(Deck $ownerDeck): static
    {
        $this->ownerDeck = $ownerDeck;

        return $this;
    }

    public function getOpponentDeck(): ?Deck
    {
        return $this->opponentDeck;
    }

    public function setOpponentDeck(Deck $opponentDeck): static
    {
        $this->opponentDeck = $opponentDeck;

        return $this;
    }

    public function setWinnerId(string $winnerId): static
    {
        $this->winnerId = $winnerId;

        return $this;
    }

    public function getWinnerId(): ?string
    {
        return $this->winnerId;
    }
}
