<?php

declare(strict_types=1);

namespace App\Game\Card\Trait;

use App\Game\AbstractCard;
use App\Game\GameContext;

trait CardAwareTrait
{
    public function onCardPlayed(AbstractCard $card, GameContext $context): void
    {
        // Default implementation does nothing
    }

    public function onCardDrawn(AbstractCard $card, GameContext $context): void
    {
        // Default implementation does nothing
    }
}
