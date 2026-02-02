<?php

declare(strict_types=1);

namespace App\Game\Badge\Handler;

use App\Entity\UserBadge;
use App\Enum\BadgeEnum;
use App\Game\Badge\BadgeEventInterface;

interface BadgeHandlerInterface
{
    public static function getBadgeKey(): BadgeEnum;

    public function handle(BadgeEventInterface $event, UserBadge $userBadge): void;
}
