<?php

declare(strict_types=1);

namespace App\Tests\Resources\Fixtures\Builder;

use App\Entity\Trade;
use App\Entity\User;
use App\Enum\TradeStatusEnum;
use App\Tests\Resources\Fixtures\ThereIs;

/**
 * @extends AbstractBuilder<Trade>
 */
class TradeBuilder extends AbstractBuilder
{
    private User $initiator;
    private User $recipient;
    private TradeStatusEnum $status = TradeStatusEnum::ACTIVE;
    private ?string $initiatorCard = null;
    private ?string $recipientCard = null;
    private bool $initiatorConfirmed = false;
    private bool $recipientConfirmed = false;

    protected function doBuild(): void
    {
        $initiator = $this->initiator ?? ThereIs::anUser()->build();
        $recipient = $this->recipient ?? ThereIs::anUser()->build();

        $this->entity = new Trade($initiator, $recipient);
        $this->entity->setStatus($this->status);
        $this->entity->setInitiatorCard($this->initiatorCard);
        $this->entity->setRecipientCard($this->recipientCard);
        $this->entity->setInitiatorConfirmed($this->initiatorConfirmed);
        $this->entity->setRecipientConfirmed($this->recipientConfirmed);
    }

    public function withInitiator(User $initiator): self
    {
        $this->initiator = $initiator;

        return $this;
    }

    public function withRecipient(User $recipient): self
    {
        $this->recipient = $recipient;

        return $this;
    }

    public function withInitiatorCard(?string $card): self
    {
        $this->initiatorCard = $card;

        return $this;
    }

    public function withRecipientCard(?string $card): self
    {
        $this->recipientCard = $card;

        return $this;
    }

    public function withInitiatorConfirmed(bool $confirmed = true): self
    {
        $this->initiatorConfirmed = $confirmed;

        return $this;
    }

    public function withRecipientConfirmed(bool $confirmed = true): self
    {
        $this->recipientConfirmed = $confirmed;

        return $this;
    }

    public function cancelled(): self
    {
        $this->status = TradeStatusEnum::CANCELLED;

        return $this;
    }
}
