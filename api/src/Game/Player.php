<?php

declare(strict_types=1);

namespace App\Game;

final class Player
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
    ) {}
}
