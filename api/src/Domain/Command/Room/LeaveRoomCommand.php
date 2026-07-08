<?php

declare(strict_types=1);

namespace App\Domain\Command\Room;

use App\Api\Serializer\CurrentResourceAwareInterface;
use App\Entity\Room;

/**
 * @implements CurrentResourceAwareInterface<Room>
 */
final class LeaveRoomCommand implements CurrentResourceAwareInterface
{
    private Room $room;

    public function getCurrentResource(): Room
    {
        return $this->room;
    }

    public function setCurrentResource(object $resource): void
    {
        $this->room = $resource;
    }
}
