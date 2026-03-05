<?php

declare(strict_types=1);

namespace App\Domain\Command\Game;

use App\Api\Serializer\CurrentResourceAwareInterface;
use App\Entity\Room;

/**
 * @implements CurrentResourceAwareInterface<Room>
 */
final class PlayGameCommand implements CurrentResourceAwareInterface
{
    private Room $currentResource;

    public function __construct(
        public readonly string $actionId,
        public readonly array $payload = [],
    ) {}

    public function setCurrentResource(object $resource): void
    {
        $this->currentResource = $resource;
    }

    public function getCurrentResource(): Room
    {
        return $this->currentResource;
    }
}
