<?php

declare(strict_types=1);

namespace App\Game\Card\Monster;

use App\Enum\CardSetEnum;

final class RedBloonsMonsterCard extends AbstractMonsterCard
{
    public static CardSetEnum $cardSet = CardSetEnum::BTD6;

    private const HEALTH_POINTS = 1;
    private const ATTACK = 1;

    public function getId(): string
    {
        return 'Redbloons';
    }

    public function getName(): string
    {
        return 'Red Bloons';
    }

    public function getDescription(): string
    {
        return 'Potit ballon tout mignon';
    }

    public function getAttack(): int
    {
        return $this->getValue(self::ATTACK, true);
    }

    public function getHealPoints(): int
    {
        return self::HEALTH_POINTS;
    }
}
