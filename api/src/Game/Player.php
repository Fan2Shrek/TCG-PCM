<?php

declare(strict_types=1);

namespace App\Game;

use App\Entity\User;

final class Player
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
    ) {}

    public static function fromUser(User $user): self
    {
        return new self((string) $user->getId(), $user->getUsername());
    }
}
