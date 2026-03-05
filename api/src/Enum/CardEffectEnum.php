<?php

declare(strict_types=1);

namespace App\Enum;

use App\Game\Card\Effect;

enum CardEffectEnum: string
{
    case HACKED = 'Hacked';
    case TORNED = 'Torned';

    public function getClass(): string
    {
        return match ($this) {
            self::HACKED => Effect\HackedCardEffect::class,
            self::TORNED => Effect\TornedCardEffect::class,
        };
    }
}
