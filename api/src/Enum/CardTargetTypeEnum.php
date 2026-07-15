<?php

declare(strict_types=1);

namespace App\Enum;

enum CardTargetTypeEnum: string
{
    case MONSTER = 'MONSTER';
    case MONSTER_AND_PASSIVE = 'MONSTER_AND_PASSIVE';
}
