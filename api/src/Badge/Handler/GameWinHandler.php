<?php

declare(strict_types=1);

namespace App\Badge\Handler;

use App\Enum\BadgeEnum;

final class GameWinHandler extends AbstractBadgeWithLevelHandler
{
    public static function getBadgeKey(): BadgeEnum
    {
        return BadgeEnum::GameWin;
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
