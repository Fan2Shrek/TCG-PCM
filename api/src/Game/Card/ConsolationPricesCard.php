<?php

declare(strict_types=1);

namespace App\Game\Card;

use App\Game\AbstractCard;
use App\Game\Card\Interface\CardAwareInterface;
use App\Game\Card\Trait\CardAwareTrait;
use App\Game\GameContext;
use App\Game\GameUtils;

final class ConsolationPricesCard extends AbstractPassiveCard implements CardAwareInterface
{
    use CardAwareTrait;

    private const int COINS_PER_DEATH = 1;

    public function getId(): string
    {
        return 'consolation_prices';
    }

    public function getName(): string
    {
        return 'Consolation Prices';
    }

    public function getDescription(): string
    {
        return GameUtils::formatDescription('Each monster death grans {{value}} gold to the player.', ['value' => $this->getValue(self::COINS_PER_DEATH)]);
    }

    public function onCardDeath(AbstractCard $card, GameContext $gameContext): void
    {
        if ($card->getOwnerId() !== $this->ownerId) {
            return;
        }

        $gameContext->addCoins($this->getValue(self::COINS_PER_DEATH, true));
    }
}
