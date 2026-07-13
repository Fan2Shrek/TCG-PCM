<?php

namespace App\Game\Card\Character;

use App\Game\Card\Interface\TurnAwareInterface;
use App\Game\Card\Trait\TurnAwareTrait;
use App\Game\GameContext;


final class CharlieCard extends AbstractCharacterCard implements TurnAwareInterface
{
    use TurnAwareTrait;

    public function getId(): string
    {
        return 'Charlie';
    }

    public function getHealthPoints(): int
    {
        return 200;
    }

    public function onTurnStart(GameContext $gameContext): void
    {
        if ($gameContext->isCurrentPlayer($this->getOwnerId())) {
            $gameContext->drawCards(1);
        }
    }
}
