<?php

declare(strict_types=1);

namespace App\Game;

use App\Enum\CardRarityEnum;
use App\Enum\CardSetEnum;
use App\Game\Card\CardState;
use App\Game\Card\Effect\AbstractCardEffect;
use App\Game\Card\EffectCollection;

abstract class AbstractCard
{
    protected EffectCollection $effects;

    private string $instanceId;

    protected string $ownerId;

    // @final to prevent child classes from having constructors with different signatures
    final public function __construct()
    {
        $this->effects = new EffectCollection();
    }

    public static CardRarityEnum $rarity = CardRarityEnum::COMMON;

    public static CardSetEnum $serie = CardSetEnum::ORIGINAL;

    abstract public function getId(): string;

    public function getInstanceId(): ?string
    {
        return $this->instanceId ?? null;
    }

    public function getOwnerId(): ?string
    {
        return $this->ownerId ?? null;
    }

    abstract public function getName(): string;

    abstract public function getDescription(): string;

    public function getImage(): string
    {
        return \sprintf('%s.png', $this->getName());
    }

    /**
     * @param bool $forceInt
     * @return ($forceInt is true ? int : float)
     */
    public function getValue(float|int $value, bool $forceInt = false): float|int
    {
        foreach ($this->effects->all() as $effect) {
            $value = $effect->modifyValue($value, $this);
        }

        return $forceInt ? (int) round($value) : (float) $value;
    }

    public function addEffect(AbstractCardEffect $effect): void
    {
        $this->effects->add($effect);
    }

    public function removeEffect(AbstractCardEffect $effect): void
    {
        $this->effects->remove($effect->getName());
    }

    public function getEffects(): EffectCollection
    {
        return $this->effects;
    }

    public function setState(CardState $state): void
    {
        if ($this->getId() !== $state->templateId) {
            throw new \InvalidArgumentException(\sprintf('Cannot add state for card %s to card %s', $state->templateId, $this->getId()));
        }

        $this->instanceId = $state->instanceId;
        $this->ownerId = $state->ownerId;
        foreach ($state->effects as $effectState) {
            $effect = $effectState->effect->getClass()::fromEffectState($effectState);

            if (!$effect instanceof AbstractCardEffect) {
                throw new \LogicException(\sprintf('Effect class %s does not implement AbstractCardEffect', $effectState->effect->getClass()));
            }

            $this->addEffect($effect);
        }
    }
}
