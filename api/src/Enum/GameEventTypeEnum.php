<?php

declare(strict_types=1);

namespace App\Enum;

enum GameEventTypeEnum: string
{
    case CARD_PLAYED = 'CARD_PLAYED';
    case CARD_DRAWN = 'CARD_DRAWN';
    case DAMAGE = 'DAMAGE';
    case HEAL = 'HEAL';
    case TURN_ENDED = 'TURN_ENDED';
    case TURN_STARTED = 'TURN_STARTED';
    case ROUND_STARTED = 'ROUND_STARTED';
    case DICE_ROLLED = 'DICE_ROLLED';
    case CARD_DISCARDED = 'CARD_DISCARDED';
    case EFFECT_ADDED = 'EFFECT_ADDED';
    case CARD_PLACE_IN_PLAY_AREA = 'CARD_PLACE_IN_PLAY_AREA';
}
