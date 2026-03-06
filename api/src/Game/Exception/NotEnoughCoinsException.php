<?php

declare(strict_types=1);

namespace App\Game\Exception;

use Throwable;

final class NotEnoughCoinsException extends GameException
{
    public function __construct(int $cost, int $actualCoint, int $code = 0, ?Throwable $previous = null)
    {
        return parent::__construct(\sprintf('Action cost %d coins, got %d', $cost, $actualCoint), $code, $previous);
    }
}
