<?php

declare(strict_types=1);

namespace App\Game\Badge;

use App\Enum\BadgeEnum;

interface BadgeEventInterface
{
    public static function geBadgeKey(): BadgeEnum;
}
