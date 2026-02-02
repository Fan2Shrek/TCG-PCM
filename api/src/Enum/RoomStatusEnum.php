<?php

declare(strict_types=1);

namespace App\Enum;

enum RoomStatusEnum: string
{
    case WAITING = 'waiting';
    case PLAYING = 'playing';
    case FINISHED = 'finished';
}
