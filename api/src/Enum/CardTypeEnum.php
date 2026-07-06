<?php

declare(strict_types=1);

namespace App\Enum;

use Symfony\Component\Translation\TranslatableMessage;

enum CardTypeEnum: string
{
    case CHARACTER = 'CHARACTER';
    case MONSTER = 'MONSTER';
    case PASSIVE = 'PASSIVE';
    case CONSUMABLE = 'CONSUMABLE';

    public function label(): TranslatableMessage
    {
        return match ($this) {
            self::CHARACTER => new TranslatableMessage('type.character', domain: 'game'),
            self::MONSTER => new TranslatableMessage('type.monster', domain: 'game'),
            self::PASSIVE => new TranslatableMessage('type.passive', domain: 'game'),
            self::CONSUMABLE => new TranslatableMessage('type.consumable', domain: 'game'),
        };
    }
}
