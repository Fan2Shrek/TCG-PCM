<?php

namespace App\Game\Card;

use App\Game\AbstractCard;
use App\Game\Card\Interface\CardAwareInterface;
use App\Game\Card\Trait\CardAwareTrait;
use App\Game\GameContext;

final class Play2HurtCard extends AbstractPassiveCard implements CardAwareInterface
{
    private const DAMAGE_AMOUNT = 2;

    use CardAwareTrait;

    public function getId(): string
    {
        return 'Play2Hurt';
    }

    public function onCardPlayed(AbstractCard $card, GameContext $gameContext): void
    {
        $gameContext->attack($this->getValue(self::DAMAGE_AMOUNT, true), $card->getOwnerId());
    }
}
