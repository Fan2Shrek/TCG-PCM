<?php

declare(strict_types=1);

namespace App\Api\DTO;

final readonly class InventoryDTO
{
    public function __construct(
        public array $cards,
    ) {}
}
