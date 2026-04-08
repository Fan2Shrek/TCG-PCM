<?php

namespace App\Game\Card;

use App\Enum\CardRarityEnum;
use App\Game\GameContext;

final class JusticeCard extends AbstractPlayableCard
{
    public static CardRarityEnum $rarity = CardRarityEnum::RARE;

    public function getId(): string
    {
        return 'Justice';
    }

    public function play(GameContext $context, array $data = []): void
    {
        $currentCount = count($context->getCurrentPlayerState()->hand);
        $otherCount = count($context->getOpponentState()->hand);

        $count = max($otherCount - $currentCount, 0);

        $context->drawCards($count);
    }
}
