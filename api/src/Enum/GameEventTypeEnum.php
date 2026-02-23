<?php

declare(strict_types=1);

namespace App\Enum;

enum GameEventTypeEnum: string
{
    case CARD_PLAYED = 'CARD_PLAYED';
    case CARD_DRAWN = 'CARD_DRAWN';
    case DAMAGE = 'DAMAGE';
    case TURN_ENDED = 'TURN_ENDED';
    case TURN_STARTED = 'TURN_STARTED';
    case ROUND_STARTED = 'ROUND_STARTED';
    case DICE_ROLLED = 'DICE_ROLLED';
}
