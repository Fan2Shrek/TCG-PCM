<?php

declare(strict_types=1);

namespace App\Event\Badge;

use App\Enum\BadgeEnum;
use App\Game\Badge\BadgeEventInterface;
use Symfony\Contracts\EventDispatcher\Event;

final class BoosterOpenedEvent extends Event implements BadgeEventInterface
{
    public static function geBadgeKey(): BadgeEnum
    {
        return BadgeEnum::OpenedBooster;
    }
}
