<?php

namespace App\Game\Card;

use App\Enum\CardRarityEnum;
use App\Game\Card\Interface\DeathAwareInterface;
use App\Game\Card\Trait\DeathAwareTrait;
use App\Game\GameContext;
use App\Game\GameUtils;

final class ImStillStandingCard extends AbstractPassiveCard implements DeathAwareInterface
{
    private const int REVIVE_HP_PERCENTAGE = 10;

    public static CardRarityEnum $rarity = CardRarityEnum::EPIC;

    use DeathAwareTrait;

    public function getId(): string
    {
        return 'ImStillStanding';
    }

    public function getImage(): string
    {
        return 'https://www.discogs.com/fr/release/590760-Elton-John-Im-Still-Standing/image/SW1hZ2U6NDMwMTU3Mjk=';
    }

    public function getDescription(): string
    {
        return GameUtils::formatDescription(parent::getDescription(), [
            'value' => $this->getValue(self::REVIVE_HP_PERCENTAGE, true),
        ]);
    }

    public function onPlayerDeath(GameContext $gameContext, string $deadPlayerId): void
    {
        if ($deadPlayerId !== $this->getOwnerId()) {
            return;
        }

        $player = $gameContext->state->getPlayer($this->getOwnerId());

        $gameContext->heal((int) round(($player->maxHealthPoints * $this->getValue(self::REVIVE_HP_PERCENTAGE)) / 100));
        $gameContext->discardCard($this->getInstanceId());
    }
}
