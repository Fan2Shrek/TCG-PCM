<?php

namespace App\Game\Card;

use App\Game\GameContext;

final class RiskyBetCard extends AbstractPlayableCard
{
    private const int OTHER_DAMAGE = 100;
    private const int SELF_DAMAGE = 50;

    public function getId(): string
    {
        return 'RiskyBet';
    }

    public function play(GameContext $context, array $data = []): void
    {
        $result = $context->rollDice(10);

        // 9 OR 10 deals damage
        if ($result >= 9) {
            $context->attack($this->getValue(self::OTHER_DAMAGE, true));
        } else {
            $context->attack($this->getValue(self::SELF_DAMAGE, true), $this->getOwnerId());
        }
    }
}
