<?php

declare(strict_types=1);

namespace App\Entity\Game;

use App\Repository\Game\ChatMessageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChatMessageRepository::class)]
final class ChatMessage
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\Column]
    private string $roomId;

    #[ORM\Column]
    private string $authorId;

    #[ORM\Column(length: 255)]
    private string $authorUsername;

    #[ORM\Column(length: 500)]
    private string $message;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(string $roomId, string $authorId, string $authorUsername, string $message)
    {
        $this->roomId = $roomId;
        $this->authorId = $authorId;
        $this->authorUsername = $authorUsername;
        $this->message = $message;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getRoomId(): string
    {
        return $this->roomId;
    }

    public function getAuthorId(): string
    {
        return $this->authorId;
    }

    public function getAuthorUsername(): string
    {
        return $this->authorUsername;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
