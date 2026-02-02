<?php

declare(strict_types=1);

namespace App\Game\Card;

use App\Game\AbstractCard;
use App\Game\GameContext;

abstract class AbstractPlayableCard extends AbstractCard
{
    abstract public function play(GameContext $context): void;
}
