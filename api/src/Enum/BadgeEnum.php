<?php

declare(strict_types=1);

namespace App\Enum;

enum BadgeEnum: string
{
    case OpenedBooster = 'opened_booster';
    case GamePlayed = 'game_played';
    case GameWin = 'game_win';
}
