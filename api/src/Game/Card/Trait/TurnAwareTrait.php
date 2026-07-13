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
        $ownerId = $this->getOwnerId();

        if (null === $ownerId || '' === $ownerId) {
            throw new \LogicException('Card ownerId is not set.');
        }

        if (!\is_string($ownerId)) {
            throw new \LogicException('Card ownerId must be a string.');
        }

        return $gameContext->isCurrentPlayer($ownerId);
    }
}
