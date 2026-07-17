<?php

namespace App\Game\Card;

use App\Enum\CardSetEnum;
use App\Game\GameContext;

final class TheHandCard extends AbstractPlayableCard
{
    public function getId(): string
    {
        return 'TheHand';
    }

    public function play(GameContext $context, array $data = []): void
    {
        $cardPool = $context->state->getOtherPlayerState()->playArea->passiveCards;
        $cardId = $context->selectRandomCardIn($cardPool);

        $context->discardCard($cardId);
    }
}
