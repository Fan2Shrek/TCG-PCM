<?php

declare(strict_types=1);

namespace App\Domain\Command\Room;

use App\Api\Serializer\CurrentResourceAwareInterface;
use App\Entity\Deck;
use App\Entity\Room;

/**
 * @implements CurrentResourceAwareInterface<Room>
 */
final class ChangeDeckCommand implements CurrentResourceAwareInterface
{
    private Room $room;

    public function __construct(
        public readonly Deck $deck,
    ) {}

    public function setCurrentResource(object $resource): void
    {
        $this->room = $resource;
    }

    public function getCurrentResource(): object
    {
        return $this->room;
    }
}
