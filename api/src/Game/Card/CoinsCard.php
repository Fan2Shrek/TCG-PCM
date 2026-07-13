<?php

namespace App\Game\Card;

use App\Game\GameContext;
use App\Game\GameUtils;

final class CoinsCard extends AbstractPlayableCard
{
    private const int COINS_GAINED = 3;

    public function getId(): string
    {
        return 'Coins';
    }

    public function getDescription(): string
    {
        return GameUtils::formatDescription(parent::getDescription(), ['value' => $this->getValue(self::COINS_GAINED, true)]);
    }

    public function play(GameContext $context, array $data = []): void
    {
        $context->addCoins($this->getValue(self::COINS_GAINED, true));
    }
}
