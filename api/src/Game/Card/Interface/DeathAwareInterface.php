<?php

declare(strict_types=1);

namespace App\Game\Card\Interface;

use App\Game\AbstractCard;
use App\Game\GameContext;

interface DeathAwareInterface
{
    public function onCardDeath(AbstractCard $card, GameContext $gameContext): void;

    public function onPlayerDeath(GameContext $gameContext, string $deadPlayerId): void;
}
