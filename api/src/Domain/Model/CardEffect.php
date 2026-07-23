<?php

declare(strict_types=1);

namespace App\Domain\Model;

final readonly class CardEffect
{
    public function __construct(
        public string $name,
        public string $description,
    ) {}
}
