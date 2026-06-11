<?php

declare(strict_types=1);

namespace App\Game\Card;

use App\Game\GameContext;

/**
 * @todo add tests but I'm a little sad rn
 */
final /* static */ class CardHelper
{
    /**
     * @return string[]
     */
    public static function getAllMonster(GameContext $context): array
    {
        return array_merge($context->getCurrentPlayerState()->playArea->monsterCards, $context->getOpponentState()->playArea->monsterCards);
    }
}
