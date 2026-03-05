<?php

declare(strict_types=1);

namespace App\Game\Card\Effect;

use App\Enum\CardEffectEnum;

final readonly class EffectState
{
    public function __construct(
        public CardEffectEnum $effect,
        public array $data = [],
    ) {}
}
