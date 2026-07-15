<?php

declare(strict_types=1);

namespace App\Tests\Resources\Fixtures\Builder;

use App\Entity\Friendship;
use App\Entity\User;
use App\Enum\FriendshipStatusEnum;
use App\Tests\Resources\Fixtures\ThereIs;

/**
 * @extends AbstractBuilder<Friendship>
 */
class FriendshipBuilder extends AbstractBuilder
{
    private User $requester;
    private User $addressee;
    private FriendshipStatusEnum $status = FriendshipStatusEnum::PENDING;

    protected function doBuild(): void
    {
        $requester = $this->requester ?? ThereIs::anUser()->build();
        $addressee = $this->addressee ?? ThereIs::anUser()->build();

        $this->entity = new Friendship($requester, $addressee);
        $this->entity->setStatus($this->status);
    }

    public function withRequester(User $requester): self
    {
        $this->requester = $requester;

        return $this;
    }

    public function withAddressee(User $addressee): self
    {
        $this->addressee = $addressee;

        return $this;
    }

    public function accepted(): self
    {
        $this->status = FriendshipStatusEnum::ACCEPTED;

        return $this;
    }
}
