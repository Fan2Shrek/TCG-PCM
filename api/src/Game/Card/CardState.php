<?php

declare(strict_types=1);

namespace App\Game\Card;

final readonly class CardState
{
    public function __construct(
        public string $instanceId,
        public string $templateId,
        public array $effects = [],
    ) {}
}
