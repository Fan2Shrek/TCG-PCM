<?php

declare(strict_types=1);

namespace App\Api\DTO;

final readonly class UserBadgesDTO
{
    /**
     * @param UserBadgeDTO[] $badges
     */
    public function __construct(
        public array $badges,
    ) {}
}
