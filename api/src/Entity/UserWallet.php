<?php

namespace App\Entity;

use App\Api\Provider\UserWalletProvider;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use App\Domain\Command\User\GenerateBoosterTokensCommand;
use App\Repository\UserWalletRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserWalletRepository::class)]
#[ApiResource(operations: [
    new Get(
        uriTemplate: '/user_wallet',
        provider: UserWalletProvider::class,
    ),
    new Post(
        uriTemplate: '/user_wallet/generate_booster_tokens',
        messenger: 'input',
        input: GenerateBoosterTokensCommand::class,
        status: 200,
    )
])]
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
