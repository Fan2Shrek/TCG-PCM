<?php

declare(strict_types=1);

namespace App\Game\Card\Trait;

use App\Game\AbstractCard;
use App\Game\GameContext;

trait CardAwareTrait
{
    public function onCardPlayed(AbstractCard $card, GameContext $gameContext): void
    {
        // Default implementation does nothing
    }

    public function onCardDrawn(string $cardId, GameContext $gameContext): void
    {
        // Default implementation does nothing
    }

    public function onCardDeath(AbstractCard $card, GameContext $gameContext): void
    {
        // Default implementation does nothing
    }
}
