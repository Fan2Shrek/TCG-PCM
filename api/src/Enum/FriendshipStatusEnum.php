<?php

declare(strict_types=1);

namespace App\Enum;

enum FriendshipStatusEnum: string
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
}
