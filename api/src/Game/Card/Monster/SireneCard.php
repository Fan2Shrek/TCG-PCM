<?php

declare(strict_types=1);

namespace App\Game\Card\Monster;

use App\Enum\CardRarityEnum;
use App\Enum\CardSetEnum;
use App\Game\Card\Interface\TurnAwareInterface;
use App\Game\GameContext;
use App\Game\GameUtils;

final class SireneCard extends AbstractMonsterCard implements TurnAwareInterface
{
    public static CardRarityEnum $rarity = CardRarityEnum::UNCOMMON;
    public static CardSetEnum $serie = CardSetEnum::BTD6;

    private const HEALTH_POINTS = 33;
    private const ATTACK = 7;
    private const TURN_DELAY = 1;

    public function getId(): string
    {
        return 'Sirene ';
    }

    public function getDescription(): string
    {
        return GameUtils::formatDescription(parent::getDescription(), [
            'value' => $this->getValue(self::TURN_DELAY, true),
        ]);
    }

    public function getBaseAttack(): int
    {
        return self::ATTACK;
    }

    public function getHealPoints(): int
    {
        return self::HEALTH_POINTS;
    }

    public function onTurnStart(GameContext $context): void
    {
        
        $targetId = $data['target'] ?? null;

        if (!\is_string($targetId)) {
            throw new \InvalidArgumentException('Missing target key');
        }

        $opponentCharacterId = $context->getOpponentState()->characterCardId;

        if ($targetId === $opponentCharacterId) {
            throw new \InvalidArgumentException('Cannot target opponent character card');
        }

        $context->stealCard($targetId, $context->getOtherPlayerId($this->ownerId), $this->ownerId);
    }

    public function onTurnEnd(GameContext $context): void
    {
    }

    public function getTurnDelay(): int
    {
        return $this->getValue(self::TURN_DELAY, true);
    }
}
