<?php

declare(strict_types=1);

namespace App\Game\Card;

use App\Game\Card\Effect\EffectState;

final readonly class CardState
{
    /**
     * @param EffectState[] $effects
     */
    public function __construct(
        public string $instanceId,
        public string $templateId,
        public string $ownerId,
        public array $effects = [],
        public array $values = [],
    ) {}

    public function addEffect(EffectState $effect): self
    {
        return clone($this, [
            'effects' => [...$this->effects, $effect],
        ]);
    }

    public function updateValues(array $newValues): self
    {
        return clone($this, [
            'values' => array_merge($this->values, $newValues),
        ]);
    }
}
