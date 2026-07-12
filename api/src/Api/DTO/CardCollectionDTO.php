<?php

declare(strict_types=1);

namespace App\Api\DTO;

final readonly class CardCollectionDTO
{
    public function __construct(
        public array $entries,
    ) {}
}
