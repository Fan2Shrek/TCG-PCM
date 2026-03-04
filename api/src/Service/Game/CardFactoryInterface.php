<?php

declare(strict_types=1);

namespace App\Service\Game;

use App\Game\AbstractCard;
use App\Game\Card\CardState;

interface CardFactoryInterface
{
    public function create(string $cardId): AbstractCard;

    public function createWithState(string $cardId, CardState $state): AbstractCard;
}
