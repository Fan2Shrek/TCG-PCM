<?php

namespace App\Game\Card;

use App\Game\Card\Interface\TurnAwareInterface;
use App\Game\Card\Trait\BaseOnTurnTrait;
use App\Game\GameContext;

final class StackyStackitoCard extends AbstractPassiveCard implements TurnAwareInterface
{
    use BaseOnTurnTrait;

    private const DELAY = 10;

    private int $delay = self::DELAY;

    public function getId(): string
    {
        return 'StackyStackito';
    }

    public function onTurnAction(GameContext $gameContext): void
    {
        $damage = $gameContext->state->getPlayer($this->getOwnerId())->coins;
        $gameContext->attack($damage);
    }

    public function getTurnDelay(): int
    {
        return $this->getValue($this->delay, true);
    }
}
