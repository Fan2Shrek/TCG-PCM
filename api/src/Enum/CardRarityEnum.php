<?php

declare(strict_types=1);

namespace App\Enum;

use Symfony\Component\Translation\TranslatableMessage;

enum CardRarityEnum: string
{
    case COMMON = 'common';
    case UNCOMMON = 'uncommon';
    case RARE = 'rare';
    case EPIC = 'epic';
    case LEGENDARY = 'legendary';

    public function label(): TranslatableMessage
    {
        return match ($this) {
            self::COMMON => new TranslatableMessage('rarity.common', domain: 'game'),
            self::UNCOMMON => new TranslatableMessage('rarity.uncommon', domain: 'game'),
            self::RARE => new TranslatableMessage('rarity.rare', domain: 'game'),
            self::EPIC => new TranslatableMessage('rarity.epic', domain: 'game'),
            self::LEGENDARY => new TranslatableMessage('rarity.legendary', domain: 'game'),
        };
    }
}
