<?php

declare(strict_types=1);

namespace App\Game\Card\Character;

use App\Game\AbstractCard;
use App\Game\Card\Trait\CardAwareTrait;

abstract class AbstractCharacterCard extends AbstractCard
{
    use CardAwareTrait;

    abstract public function getHealthPoints(): int;
}
