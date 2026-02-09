<?php

declare(strict_types=1);

namespace App\Enum;

enum GameEventTypeEnum: string
{
    case CARD_PLAYED = 'CARD_PLAYED';
    case CARD_DRAWN = 'CARD_DRAWN';
    case DAMAGE = 'DAMAGE';
}
