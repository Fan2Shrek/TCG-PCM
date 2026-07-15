<?php

declare(strict_types=1);

namespace App\Enum;

enum TradeStatusEnum: string
{
    case ACTIVE = 'active';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
}
