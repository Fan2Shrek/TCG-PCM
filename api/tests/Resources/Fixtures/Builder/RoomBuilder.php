<?php

declare(strict_types=1);

namespace App\Tests\Resources\Fixtures\Builder;

use App\Entity\Room;
use App\Entity\User;
use App\Tests\Resources\Fixtures\ThereIs;

/**
* @extends AbstractBuilder<Room>
*/
final class RoomBuilder extends AbstractBuilder
{
    private User $owner;

    protected function doBuild(): void
    {
        $owner = $this->owner ?? ThereIs::anUser()->build();

        $this->entity = new Room($owner);
    }

    public function withOwner($owner): self
    {
        $this->owner = $owner;

        return $this;
    }
}
