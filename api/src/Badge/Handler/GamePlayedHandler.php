<?php

declare(strict_types=1);

namespace App\Badge\Handler;

use App\Enum\BadgeEnum;

class GamePlayedHandler extends AbstractBadgeWithLevelHandler
{
    public static function getBadgeKey(): BadgeEnum
    {
        return BadgeEnum::GamePlayed;
    }

    protected function getLevels(): array
    {
        return [
            1 => 1,
            2 => 10,
            3 => 50,
            4 => 200,
            5 => 500,
        ];
    }
}
