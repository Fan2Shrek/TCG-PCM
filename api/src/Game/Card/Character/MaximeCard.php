<?php

namespace App\Game\Card\Character;

use App\Game\Card\Interface\TurnAwareInterface;
use App\Game\Card\Trait\BaseOnTurnTrait;

use App\Game\GameContext;
use App\Game\GameUtils;

final class MaximeCard extends AbstractCharacterCard implements TurnAwareInterface
{
    use BaseOnTurnTrait;

    private const TURN_DELAY = 2;

    public function getId(): string
    {
        return 'Maxime';
    }

    public function getHealthPoints(): int
    {
        return 330;
    }

    public function getDescription(): string
    {
        return GameUtils::formatDescription(parent::getDescription(), [
            'value1' => 1,
            'value2' => $this->getTurnDelay(),
        ]);
    }

    public function onTurnAction(GameContext $gameContext): void
    {
        $cardId = $gameContext->getOneRandomCard($gameContext->getOtherPlayerId($this->ownerId));

        $gameContext->discardCard($cardId);
    }

    public function getTurnDelay(): int
    {
        return $this->getValue(self::TURN_DELAY, true);
    }
}
