<?php

declare(strict_types=1);

namespace App\Domain\Command\Room;

use App\Api\Serializer\CurrentResourceAwareInterface;
use App\Entity\Room;

/**
* @implements CurrentResourceAwareInterface<Room>
*/
final class JoinRoomCommand implements CurrentResourceAwareInterface
{
    private Room $room;

    public function getCurrentResource(): Room
    {
        return $this->room;
    }

    public function setCurrentResource(object $room): void
    {
        $this->room = $room;
    }
}
