<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\UserInfoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserInfoRepository::class)]
#[ApiResource]
class UserInfo
{
    #[ORM\Id]
    #[ORM\Column]
    private int $id;

    #[ORM\Column]
    private \DateTimeImmutable $lastBoosterTokensAt;

    #[ORM\OneToOne(inversedBy: 'userInfo', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    public function __construct(User $user)
    {
        $this->id = $user->getId();
        $this->user = $user;
        $this->lastBoosterTokensAt = new \DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getLastBoosterTokensAt(): \DateTimeImmutable
    {
        return $this->lastBoosterTokensAt;
    }

    public function setLastBoosterTokensAt(\DateTimeImmutable $lastBoosterTokensAt): static
    {
        $this->lastBoosterTokensAt = $lastBoosterTokensAt;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
