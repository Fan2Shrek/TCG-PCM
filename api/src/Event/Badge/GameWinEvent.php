<?php

declare(strict_types=1);

namespace App\Event\Badge;

use App\Badge\BadgeEventInterface;
use App\Entity\User;
use App\Enum\BadgeEnum;
use Symfony\Contracts\EventDispatcher\Event;

final class GameWinEvent extends Event implements BadgeEventInterface
{
    public function __construct(private readonly User $user) {}

    public static function getBadgeKey(): BadgeEnum
    {
        return BadgeEnum::GameWin;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
