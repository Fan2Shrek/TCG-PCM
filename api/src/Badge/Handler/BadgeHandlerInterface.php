<?php

declare(strict_types=1);

namespace App\Badge\Handler;

use App\Badge\BadgeEventInterface;
use App\Entity\UserBadge;
use App\Enum\BadgeEnum;

interface BadgeHandlerInterface
{
    public static function getBadgeKey(): BadgeEnum;

    public function handle(BadgeEventInterface $event, UserBadge $userBadge): void;

    /**
     * @return array<int, int>
     */
    public function getThresholds(): array;
}
