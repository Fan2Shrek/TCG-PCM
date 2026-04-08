<?php

declare(strict_types=1);

namespace App\Game\Card\Trait;

use App\Game\AbstractCard;
use App\Game\GameContext;

trait DeathAwareTrait
{
    public function onCardDeath(AbstractCard $card, GameContext $gameContext): void
    {
        // Default implementation does nothing
    }

    public function onPlayerDeath(GameContext $gameContext, string $deadPlayerId): void
    {
        // Default implementation does nothing
    }
}
