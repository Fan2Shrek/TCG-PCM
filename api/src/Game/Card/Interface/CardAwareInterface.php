<?php

declare(strict_types=1);

namespace App\Game\Card\Interface;

use App\Game\AbstractCard;
use App\Game\GameContext;

interface CardAwareInterface
{
    public function onCardPlayed(AbstractCard $card, GameContext $gameContext): void;

    public function onCardDrawn(AbstractCard $card, GameContext $gameContext);
}
