<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Api\Provider\PendingFriendRequestsProvider;
use App\Api\Provider\UserFriendshipsProvider;
use App\Domain\Command\Friendship\AcceptFriendRequestCommand;
use App\Domain\Command\Friendship\CancelFriendRequestCommand;
use App\Domain\Command\Friendship\DeclineFriendRequestCommand;
use App\Domain\Command\Friendship\RemoveFriendCommand;
use App\Domain\Command\Friendship\SendFriendRequestCommand;
use App\Enum\FriendshipStatusEnum;
use App\Repository\FriendshipRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ApiResource(operations: [
    new GetCollection(uriTemplate: '/friendships', provider: UserFriendshipsProvider::class, normalizationContext: [
        'groups' => ['api:friendship:read'],
    ]),
    new GetCollection(uriTemplate: '/pending-friend-requests', provider: PendingFriendRequestsProvider::class, normalizationContext: [
        'groups' => ['api:friendship:read'],
    ]),

    new Post(
        uriTemplate: '/friendships',
        messenger: 'input',
        normalizationContext: ['groups' => ['api:friendship:read']],
        input: SendFriendRequestCommand::class,
        status: 201,
    ),
    new Post(uriTemplate: '/friendships/{id}/accept', messenger: 'input', input: AcceptFriendRequestCommand::class, status: 204),
    new Post(uriTemplate: '/friendships/{id}/decline', messenger: 'input', input: DeclineFriendRequestCommand::class, status: 204),
    new Post(uriTemplate: '/friendships/{id}/cancel', messenger: 'input', input: CancelFriendRequestCommand::class, status: 204),
    new Post(uriTemplate: '/friendships/{id}/remove', messenger: 'input', input: RemoveFriendCommand::class, status: 204),
])]
#[ORM\Entity(repositoryClass: FriendshipRepository::class)]
class Friendship
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private User $requester;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private User $addressee;

    #[ORM\Column(enumType: FriendshipStatusEnum::class)]
    private FriendshipStatusEnum $status;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $respondedAt = null;

    public function __construct(User $requester, User $addressee)
    {
        $this->requester = $requester;
        $this->addressee = $addressee;
        $this->status = FriendshipStatusEnum::PENDING;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getRequester(): User
    {
        return $this->requester;
    }

    public function getAddressee(): User
    {
        return $this->addressee;
    }

    public function getStatus(): FriendshipStatusEnum
    {
        return $this->status;
    }

    public function setStatus(FriendshipStatusEnum $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getRespondedAt(): ?\DateTimeImmutable
    {
        return $this->respondedAt;
    }

    public function setRespondedAt(?\DateTimeImmutable $respondedAt): static
    {
        $this->respondedAt = $respondedAt;

        return $this;
    }

    public function involves(User $user): bool
    {
        return $this->requester === $user || $this->addressee === $user;
    }

    public function getOtherUser(User $user): User
    {
        return $this->requester === $user ? $this->addressee : $this->requester;
    }
}
