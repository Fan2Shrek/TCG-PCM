<?php

declare(strict_types=1);

namespace App\Game\Card\Trait;

use App\Enum\GameEventTypeEnum;
use App\Game\AbstractCard;
use App\Game\Card\CardState;
use App\Game\GameContext;

trait BaseOnTurnTrait
{
    private int $turnRemainingBeforeAction;

    use TurnAwareTrait;

    public function onTurnStart(GameContext $gameContext): void
    {
        $this->turnRemainingBeforeAction--;

        if ($this->turnRemainingBeforeAction <= 0) {
            $this->onTurnAction($gameContext);

            $this->turnRemainingBeforeAction = $this->getTurnDelay();
        }

        $gameContext->pushGameEvent(GameEventTypeEnum::UPDATE_CARD_STATE, [
            'cardId' => $this->getInstanceId(),
            'stateToUpdate' => [
                'turnRemainingBeforeAction' => $this->turnRemainingBeforeAction,
            ],
        ]);
    }

    abstract public function getTurnDelay(): int;

    public function setState(CardState $state): void
    {
        parent::setState($state);

        $this->initFromState($state);
    }

    abstract private function onTurnAction(GameContext $gameContext): void;

    private function initFromState(CardState $state): void
    {
        $this->turnRemainingBeforeAction = $state->values['turnRemainingBeforeAction'] ?? $this->getTurnDelay();
    }
}
