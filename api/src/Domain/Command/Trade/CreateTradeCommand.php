<?php

declare(strict_types=1);

namespace App\Domain\Command\Trade;

final class CreateTradeCommand
{
    public function __construct(
        public readonly int $friendId,
    ) {}
}
