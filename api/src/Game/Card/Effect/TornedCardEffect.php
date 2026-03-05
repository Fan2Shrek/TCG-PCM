<?php

declare(strict_types=1);

namespace App\Game\Card\Effect;

use App\Enum\CardEffectEnum;
use App\Enum\GameEventTypeEnum;
use App\Game\AbstractCard;
use App\Game\GameContext;

final class TornedCardEffect extends AbstractCardEffect
{
    public const int FAIL_CHANCE = 30;

    public static function getName(): CardEffectEnum
    {
        return CardEffectEnum::TORNED;
    }

    public function beforeAction(AbstractCard $card, GameContext $gameContext): void
    {
        $result = $gameContext->rollDice(100);

        if ($result > self::FAIL_CHANCE) {
            return;
        }

        $gameContext->pushGameEvent(GameEventTypeEnum::CARD_ACTION_PREVENTED, [
            'cardId' => $card->getInstanceId(),
            'reason' => $this->getName()->value,
        ]);
    }
}
