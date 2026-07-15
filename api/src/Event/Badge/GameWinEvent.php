<?php

declare(strict_types=1);

namespace App\Event\Badge;

use App\Badge\BadgeEventInterface;
use App\Enum\BadgeEnum;
use Symfony\Contracts\EventDispatcher\Event;

final class GameWinEvent extends Event implements BadgeEventInterface
{
    public static function getBadgeKey(): BadgeEnum
    {
        return BadgeEnum::GameWin;
    }
}
