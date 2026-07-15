<?php

declare(strict_types=1);

namespace App\Badge;

use App\Entity\User;
use App\Enum\BadgeEnum;

interface BadgeEventInterface
{
    public static function getBadgeKey(): BadgeEnum;

    public function getUser(): User;
}
