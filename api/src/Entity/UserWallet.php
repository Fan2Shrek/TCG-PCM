<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\UserWalletRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserWalletRepository::class)]
#[ApiResource(operations: [])]
class UserWallet
{
    #[ORM\Id]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(type: Types::SMALLINT)]
    private int $boosterTokens = 0;

    #[ORM\OneToOne(inversedBy: 'userWallet', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    public function __construct(User $user, int $boosterTokens = 0)
    {
        $this->id = $user->getId();
        $this->user = $user;
        $this->boosterTokens = $boosterTokens;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getBoosterTokens(): int
    {
        return $this->boosterTokens;
    }

    public function setBoosterTokens(int $boosterTokens): static
    {
        $this->boosterTokens = $boosterTokens;

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
