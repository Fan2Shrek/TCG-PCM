<?php

namespace App\Game\Card\Character;

use App\Game\Card\Interface\TurnAwareInterface;
use App\Game\Card\Trait\BaseOnTurnTrait;
use App\Game\GameContext;
use App\Game\GameUtils;

final class MaximeCard extends AbstractCharacterCard implements TurnAwareInterface
{
    use BaseOnTurnTrait;

    private const TURN_DELAY = 3;
    private const PLAYER_DAMAGE = 25;
    private const CARD_EATEN = 1;

    public function getId(): string
    {
        return 'Maxime';
    }

    public function getImage(): string
    {
        return 'maxime.webp';
    }

    public function getHealthPoints(): int
    {
        return 250;
    }

    public function getDescription(): string
    {
        return GameUtils::formatDescription(parent::getDescription(), [
            'value1' => self::CARD_EATEN,
            'value2' => $this->getTurnDelay(),
            'value3' => self::PLAYER_DAMAGE,
        ]);
    }

    private function onTurnAction(GameContext $gameContext): void
    {
        for ($i = 0; $i < self::CARD_EATEN; $i++) {
            $this->eatRandomCard($gameContext);
        }
    }

    public function getTurnDelay(): int
    {
        return $this->getValue(self::TURN_DELAY, true);
    }

    private function eatRandomCard(GameContext $gameContext): void
    {
        $targetId = $gameContext->getOneRandomCard($gameContext->getOtherPlayerId($this->ownerId));

        $opponentCharacterId = $gameContext->getOpponentState()->characterCardId;

        if ($targetId === $opponentCharacterId) {
            $opponentId = $gameContext->getOtherPlayerId($this->ownerId);
            $gameContext->attack(self::PLAYER_DAMAGE, $opponentId);

            return;
        }

        $gameContext->discardCard($targetId);
    }
}
