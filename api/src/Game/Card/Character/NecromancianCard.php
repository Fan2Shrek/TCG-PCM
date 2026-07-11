<?php

namespace App\Game\Card\Character;

use App\Enum\CardRarityEnum;
use App\Enum\GameEventTypeEnum;
use App\Game\Card\Interface\TurnAwareInterface;
use App\Game\Card\Trait\TurnAwareTrait;
use App\Game\GameContext;

final class NecromancianCard extends AbstractCharacterCard implements TurnAwareInterface
{
    public static CardRarityEnum $rarity = CardRarityEnum::EPIC;

    use TurnAwareTrait;

    public function getId(): string
    {
        return 'Necromancian';
    }

    public function getHealthPoints(): int
    {
        return 150;
    }

    public function onTurnEnd(GameContext $gameContext): void
    {
        if (!$this->isOwnerTurn($gameContext)) {
            return;
        }

        $owner = $gameContext->state->getPlayer($this->getOwnerId());
        $pile = $owner->discardPile;

        if (count($pile) > 0) {
            $index = $gameContext->randomIntBetween(0, count($pile));
            $cardId = array_keys($pile)[$index];

            $gameContext->pushGameEvent(GameEventTypeEnum::CARD_REDRAWN, [
                'playerId' => $this->getOwnerId(),
                'cardId' => $cardId,
            ]);
        }
    }
}
