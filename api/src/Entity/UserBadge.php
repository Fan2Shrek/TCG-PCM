<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Api\Provider\UserBadgesProvider;
use App\Enum\BadgeEnum;
use App\Repository\UserBadgeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserBadgeRepository::class)]
#[ApiResource(operations: [
    new Get(uriTemplate: '/badges', provider: UserBadgesProvider::class),
])]
class UserBadge
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column]
    private int $level;

    #[ORM\Column]
    private int $score = 0;

    #[ORM\ManyToOne(inversedBy: 'userBadges')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(enumType: BadgeEnum::class)]
    private BadgeEnum $badgeName;

    public function __construct(User $user, BadgeEnum $badgeName)
    {
        $this->user = $user;
        $this->badgeName = $badgeName;
        $this->level = 1;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): static
    {
        $this->level = $level;

        return $this;
    }

    public function getScore(): int
    {
        return $this->score;
    }

    public function setScore(int $score): static
    {
        $this->score = $score;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getBadgeName(): BadgeEnum
    {
        return $this->badgeName;
    }

    public function setBadgeName(BadgeEnum $badgeName): static
    {
        $this->badgeName = $badgeName;

        return $this;
    }
}
