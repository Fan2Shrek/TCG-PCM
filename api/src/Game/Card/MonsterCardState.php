<?php

declare(strict_types=1);

namespace App\Game\Card;

use App\Game\Card\Effect\EffectState;

final readonly class MonsterCardState extends CardState
{
    /**
     * @param EffectState[] $effects
     */
    public function __construct(
        string $instanceId,
        string $templateId,
        string $ownerId,
        public int $currentHealthPoints,
        array $effects = [],
        array $values = [],
        public bool $canAttack = true,
    ) {
        parent::__construct($instanceId, $templateId, $ownerId, $effects, $values);
    }

    public static function fromParent(CardState $state, int $currentHealthPoints): self
    {
        return new self($state->instanceId, $state->templateId, $state->ownerId, $currentHealthPoints, $state->effects, $state->values);
    }

    #[\NoDiscard]
    public function withCurrentHealthPoints(int $currentHealthPoints): self
    {
        return clone($this, [
            'currentHealthPoints' => $currentHealthPoints,
        ]);
    }

    #[\NoDiscard]
    public function withCanAttack(bool $canAttack): self
    {
        return clone($this, [
            'canAttack' => $canAttack,
        ]);
    }
}
