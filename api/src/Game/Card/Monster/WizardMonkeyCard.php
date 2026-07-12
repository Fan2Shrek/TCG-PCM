<?php

declare(strict_types=1);

namespace App\Game\Card\Monster;

use App\Enum\CardRarityEnum;
use App\Enum\CardSetEnum;
use App\Game\GameContext;
use App\Game\GameUtils;

final class WizardMonkeyCard extends AbstractMonsterCard
{
    public static CardRarityEnum $rarity = CardRarityEnum::EPIC;
    public static CardSetEnum $serie = CardSetEnum::BTD6;

    private const HEALTH_POINTS = 15;
    private const ATTACK = 8;
    private const AOE_DAMAGE = 8;

    public function getId(): string
    {
        return 'WizardMonkey';
    }

    public function getDescription(): string
    {
        return GameUtils::formatDescription(parent::getDescription(), [
            'value' => $this->getValue(self::AOE_DAMAGE, true),
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

    public function onMonsterPlayed(GameContext $context): void
    {
        $ownerId = $this->getOwnerId();

        if (null === $ownerId) {
            return;
        }

        $opponentState = $context->getPlayerStateById($context->getOtherPlayerId($ownerId));

        foreach ($opponentState->playArea->monsterCards as $targetId) {
            $context->damageCard($targetId, $this->getValue(self::AOE_DAMAGE, true));
        }
    }
}
