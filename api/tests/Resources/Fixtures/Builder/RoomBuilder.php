<?php

declare(strict_types=1);

namespace App\Tests\Resources\Fixtures\Builder;

use App\Entity\Room;
use App\Entity\User;
use App\Tests\Resources\Fixtures\ThereIs;

/**
 * @extends AbstractBuilder<Room>
 */
class RoomBuilder extends AbstractBuilder
{
    private User $owner;
    private User $opponent;

    protected function doBuild(): void
    {
        $owner = $this->owner ?? ThereIs::anUser()->build();

        $this->entity = new Room($owner);
        $this->entity->setOwnerDeck(ThereIs::aDeck()->ownedBy($owner)->build());

        if ($this->opponent ?? null) {
            $this->entity->setOpponent($this->opponent);
            $this->entity->setOpponentDeck(ThereIs::aDeck()->ownedBy($this->opponent)->build());
        }
    }

    public function withOwner($owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function withOpponent(?User $opponent = null): self
    {
        $this->opponent = $opponent ?? ThereIs::anUser()->build();

        return $this;
    }
}
