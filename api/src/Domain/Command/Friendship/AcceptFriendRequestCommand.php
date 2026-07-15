<?php

declare(strict_types=1);

namespace App\Domain\Command\Friendship;

use App\Api\Serializer\CurrentResourceAwareInterface;
use App\Entity\Friendship;

/**
 * @implements CurrentResourceAwareInterface<Friendship>
 */
final class AcceptFriendRequestCommand implements CurrentResourceAwareInterface
{
    private Friendship $friendship;

    public function getCurrentResource(): Friendship
    {
        return $this->friendship;
    }

    public function setCurrentResource(object $resource): void
    {
        $this->friendship = $resource;
    }
}
