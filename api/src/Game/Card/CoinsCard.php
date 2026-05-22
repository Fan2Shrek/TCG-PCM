<?php

namespace App\Game\Card;

use App\Game\GameContext;

final class CoinsCard extends AbstractPlayableCard
{
    private const int COINS_GAINED = 5;

    public function getId(): string
    {
        return 'Coins';
    }

    public function play(GameContext $context, array $data = []): void
    {
        $context->addCoins($this->getValue(self::COINS_GAINED, true));
    }
}
