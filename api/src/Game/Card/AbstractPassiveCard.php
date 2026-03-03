<?php

declare(strict_types=1);

namespace App\Game\Card;

use App\Game\AbstractCard;
use App\Game\GameContext;

abstract class AbstractPassiveCard extends AbstractCard
{
    public function onCardPlace(GameContext $gameContext): void
    {
        // no-op by default
    }
}
