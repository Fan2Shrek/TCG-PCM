<?php

namespace App\Domain\Command\Room;

use App\Api\Serializer\CurrentResourceAwareInterface;
use App\Entity\Room;

/**
 * @implements CurrentResourceAwareInterface<Room>
 */
final class TogglePrivateRoomCommand implements CurrentResourceAwareInterface
{
    private Room $room;

    public function __construct(
        public readonly bool $isPrivate,
    ) {
    }

    public function getCurrentResource(): Room
    {
        return $this->room;
    }

    public function setCurrentResource(object $resource): void
    {
        $this->room = $resource;
    }
}
