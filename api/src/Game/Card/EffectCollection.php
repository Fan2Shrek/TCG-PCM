<?php

declare(strict_types=1);

namespace App\Game\Card;

use App\Enum\CardEffectEnum;
use App\Game\Card\Effect\AbstractCardEffect;

final class EffectCollection
{
    /**
     * @var AbstractCardEffect[]
     */
    protected array $effects = [];

    public function add(AbstractCardEffect $effect): self
    {
        $this->effects[$effect->getName()->value] = $effect;

        return $this;
    }

    public function remove(CardEffectEnum $effect): self
    {
        unset($this->effects[$effect->value]);

        return $this;
    }

    public function hasEffect(CardEffectEnum $effect): bool
    {
        return \array_key_exists($effect->value, $this->effects);
    }

    public function get(CardEffectEnum $effect): ?AbstractCardEffect
    {
        return $this->effects[$effect->value] ?? null;
    }

    /**
     * @return AbstractCardEffect[]
     */
    public function all(): array
    {
        return array_values($this->effects);
    }
}
