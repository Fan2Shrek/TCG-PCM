<?php

namespace App\Game\Card\Monster;

use App\Enum\CardRarityEnum;
use App\Game\GameContext;

final class ViciousBeeCard extends AbstractMonsterCard
{
    public static CardRarityEnum $rarity = CardRarityEnum::RARE;

    private const HEALTH_POINTS = 10;
    private const ATTACK = 5;

    public function getId(): string
    {
        return 'ViciousBee';
    }

    public function getBaseAttack(): int
    {
        return self::ATTACK;
    }

    public function onMonsterDeath(GameContext $context): void
    {
        $context->giveCard('ViciousStinger', $this->getOwnerId());
    }

    public function getHealPoints(): int
    {
        return self::HEALTH_POINTS;
    }
}
