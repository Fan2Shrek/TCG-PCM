<?php

declare(strict_types=1);

namespace App\Api\DTO;

final readonly class UserBadgeDTO
{
    public function __construct(
        public string $badgeName,
        public int $level,
        public int $score,
        public int $currentThreshold,
        public ?int $nextThreshold,
    ) {}
}
