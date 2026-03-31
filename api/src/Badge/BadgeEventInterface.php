<?php

declare(strict_types=1);

namespace App\Badge;

use App\Enum\BadgeEnum;

interface BadgeEventInterface
{
    public static function getBadgeKey(): BadgeEnum;
}
