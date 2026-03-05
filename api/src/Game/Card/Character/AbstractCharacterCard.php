<?php

declare(strict_types=1);

namespace App\Game\Card\Character;

use App\Game\AbstractCard;

abstract class AbstractCharacterCard extends AbstractCard
{
    abstract public function getHealthPoints(): int;
}
