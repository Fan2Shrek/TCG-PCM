<?php

declare(strict_types=1);

namespace App\Domain\Command\Friendship;

final class SendFriendRequestCommand
{
    public function __construct(
        public readonly string $username,
    ) {}
}
