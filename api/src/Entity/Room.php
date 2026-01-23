<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Domain\Command\Room\CreateRoomCommand;
use App\Repository\RoomRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/rooms/create',
            messenger: 'input',
            input: CreateRoomCommand::class,
            status: 201,
        ),
    ],
)]
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

    public function __construct(User $owner)
    {
        $this->owner = $owner;
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
}
