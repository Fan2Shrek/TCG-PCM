<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Api\Provider\UserActiveTradeProvider;
use App\Domain\Command\Trade\CancelTradeCommand;
use App\Domain\Command\Trade\ConfirmTradeCommand;
use App\Domain\Command\Trade\CreateTradeCommand;
use App\Domain\Command\Trade\OfferCardCommand;
use App\Domain\Command\Trade\SubscribeTradeCommand;
use App\Enum\TradeStatusEnum;
use App\Repository\TradeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ApiResource(operations: [
    new Get(uriTemplate: '/trades/{id}', normalizationContext: ['groups' => ['api:trade:read']]),
    new GetCollection(uriTemplate: '/me/trade', provider: UserActiveTradeProvider::class, normalizationContext: [
        'groups' => ['api:trade:read'],
    ]),

    new Post(uriTemplate: '/trades', messenger: 'input', normalizationContext: ['groups' => ['api:trade:read']], input: CreateTradeCommand::class, status: 201),
    new Post(uriTemplate: '/trades/{id}/offer', messenger: 'input', input: OfferCardCommand::class, status: 204),
    new Post(
        uriTemplate: '/trades/{id}/confirm',
        messenger: 'input',
        normalizationContext: ['groups' => ['api:trade:read']],
        input: ConfirmTradeCommand::class,
        status: 200,
    ),
    new Post(uriTemplate: '/trades/{id}/cancel', messenger: 'input', input: CancelTradeCommand::class, status: 204),
    new Post(uriTemplate: '/trades/{id}/subscribe', messenger: 'input', input: SubscribeTradeCommand::class, status: 200),
])]
#[ORM\Entity(repositoryClass: TradeRepository::class)]
class Trade
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private User $initiator;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private User $recipient;

    #[ORM\Column(enumType: TradeStatusEnum::class)]
    private TradeStatusEnum $status;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $initiatorCard = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $recipientCard = null;

    #[ORM\Column]
    private bool $initiatorConfirmed = false;

    #[ORM\Column]
    private bool $recipientConfirmed = false;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $completedAt = null;

    public function __construct(User $initiator, User $recipient)
    {
        $this->initiator = $initiator;
        $this->recipient = $recipient;
        $this->status = TradeStatusEnum::ACTIVE;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getInitiator(): User
    {
        return $this->initiator;
    }

    public function getRecipient(): User
    {
        return $this->recipient;
    }

    public function getStatus(): TradeStatusEnum
    {
        return $this->status;
    }

    public function setStatus(TradeStatusEnum $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getInitiatorCard(): ?string
    {
        return $this->initiatorCard;
    }

    public function setInitiatorCard(?string $initiatorCard): static
    {
        $this->initiatorCard = $initiatorCard;

        return $this;
    }

    public function getRecipientCard(): ?string
    {
        return $this->recipientCard;
    }

    public function setRecipientCard(?string $recipientCard): static
    {
        $this->recipientCard = $recipientCard;

        return $this;
    }

    public function isInitiatorConfirmed(): bool
    {
        return $this->initiatorConfirmed;
    }

    public function setInitiatorConfirmed(bool $initiatorConfirmed): static
    {
        $this->initiatorConfirmed = $initiatorConfirmed;

        return $this;
    }

    public function isRecipientConfirmed(): bool
    {
        return $this->recipientConfirmed;
    }

    public function setRecipientConfirmed(bool $recipientConfirmed): static
    {
        $this->recipientConfirmed = $recipientConfirmed;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getCompletedAt(): ?\DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?\DateTimeImmutable $completedAt): static
    {
        $this->completedAt = $completedAt;

        return $this;
    }

    public function involves(User $user): bool
    {
        return $this->initiator === $user || $this->recipient === $user;
    }

    public function getOtherUser(User $user): User
    {
        return $this->initiator === $user ? $this->recipient : $this->initiator;
    }
}
