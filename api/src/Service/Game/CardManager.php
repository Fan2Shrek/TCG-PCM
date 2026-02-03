<?php

declare(strict_types=1);

namespace App\Service\Game;

use App\Game\AbstractCard;

final class CardManager
{
    /**
    * @template T of AbstractCard
    *
    * @param class-string<T> $class
    *
    * @return T
    */
    public function initiateCard(string $class): AbstractCard
    {
        return new $class();
    }
}
