<?php

declare(strict_types=1);

namespace App\Game\Card\Trait;

use App\Game\AbstractCard;
use App\Game\GameContext;

trait TurnAwareTrait
{
    public function onTurnStart(GameContext $gameContext): void
    {
        // Default implementation does nothing
    }

    public function onTurnEnd(GameContext $gameContext): void
    {
        // Default implementation does nothing
    }

    protected function isOwnerTurn(GameContext $gameContext): bool
    {
        assert($this instanceof AbstractCard, 'Must be AbstractCard');

        if (!$this->getOwnerId()) {
            throw new \LogicException('Card ownerId is not set.');
        }

        return $gameContext->isCurrentPlayer($this->getOwnerId());
    }
}
