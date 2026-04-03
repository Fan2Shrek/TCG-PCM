<?php

declare(strict_types=1);

namespace App\Domain\Command\Booster;

final readonly class OpenBoosterCommand
{
    public function __construct(
        public string $type,
    ) {}
}
