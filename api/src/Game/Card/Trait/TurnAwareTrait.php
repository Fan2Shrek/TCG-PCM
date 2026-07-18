<?php

declare(strict_types=1);

namespace App\Game\Card\Trait;

use App\Game\AbstractCard;
use App\Game\GameContext;
use App\Game\State\GameEvent;

trait TurnAwareTrait
{
    public function onTurnStart(GameEvent $event, GameContext $gameContext): void
    {
        // Default implementation does nothing
    }

    public function onTurnEnd(GameEvent $event, GameContext $gameContext): void
    {
        // Default implementation does nothing
    }

    protected function isOwnerTurn(GameEvent $event): bool
    {
        assert($this instanceof AbstractCard, 'Must be AbstractCard');
        $ownerId = $this->getOwnerId();

        if (null === $ownerId || '' === $ownerId) {
            throw new \LogicException('Card ownerId is not set.');
        }

        if (!\is_string($ownerId)) {
            throw new \LogicException('Card ownerId must be a string.');
        }

        $playerId = $event->data['playerId'] ?? null;

        if (null === $playerId || '' === $playerId) {
            throw new \LogicException('Event playerId is not set.');
        }

        return $ownerId === $playerId;
    }
}
