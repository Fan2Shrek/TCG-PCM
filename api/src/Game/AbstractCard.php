<?php

declare(strict_types=1);

namespace App\Game;

use App\Enum\CardRarityEnum;

abstract class AbstractCard
{
    // @final to prevent child classes from having constructors with different signatures
    final public function __construct() {}

    public static CardRarityEnum $rarity = CardRarityEnum::COMMON;

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
}
