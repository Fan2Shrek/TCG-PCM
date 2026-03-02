<?php

declare(strict_types=1);

namespace App\Game;

use App\Enum\CardRarityEnum;
use App\Game\Card\CardState;
use App\Game\Card\Effect\AbstractCardEffect;
use App\Game\Card\EffectCollection;

abstract class AbstractCard
{
    protected EffectCollection $effects;

    private string $instanceId;

    // @final to prevent child classes from having constructors with different signatures
    final public function __construct(?string $instanceId = null)
    {
        $this->effects = new EffectCollection();

        if ($instanceId) {
            $this->instanceId = $instanceId;
        }
    }

    public static CardRarityEnum $rarity = CardRarityEnum::COMMON;

    abstract public function getId(): string;

    public function getInstanceId(): string
    {
        return $this->instanceId;
    }

    abstract public function getName(): string;

    abstract public function getDescription(): string;

    public function getImage(): string
    {
        return \sprintf('%s.png', $this->getName());
    }

    public function onCardPlayed(self $card, GameContext $context): void
    {
        // Default implementation does nothing
    }

    public function onCardDrawn(self $card, GameContext $context): void
    {
        // Default implementation does nothing
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

    public function setState(CardState $state): void
    {
        if ($this->getId() !== $state->templateId) {
            throw new \LogicException(\sprintf('Cannot add state for card %s to card %s', $state->templateId, $this->getId()));
        }

        $this->instanceId = $state->instanceId;
        foreach ($state->effects as $effectState) {
            $effect = $effectState->effect->getClass()::fromEffectState($effectState);

            if (!$effect instanceof AbstractCardEffect) {
                throw new \LogicException(\sprintf('Effect class %s does not implement AbstractCardEffect', $effectState->effect->getClass()));
            }

            $this->addEffect($effect);
        }
    }
}
