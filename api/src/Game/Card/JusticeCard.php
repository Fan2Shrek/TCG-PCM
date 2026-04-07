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

    public function getName(): string
    {
        return 'Justice';
    }

    public function getDescription(): string
    {
        return 'Make current player draw has many cards equal to the number of cards in other player hand.';
    }

    public function play(GameContext $context, array $data = []): void
    {
        $currentCount = count($context->getCurrentPlayerState()->hand);
        $otherCount = count($context->getOpponentState()->hand);

        $count = max($otherCount - $currentCount, 0);

        $context->drawCards($count);
    }
}
