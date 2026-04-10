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

    public function getImage(): string
    {
        return 'https://www.google.com/url?sa=t&source=web&rct=j&url=https%3A%2F%2Fbloons-td-battles.fandom.com%2Fwiki%2FRed_Bloon&ved=0CBYQjRxqFwoTCOjs45WFnZMDFQAAAAAdAAAAABAH&opi=89978449';
    }

    public function getBaseAttack(): int
    {
        return self::ATTACK;
    }

    public function getHealPoints(): int
    {
        return self::HEALTH_POINTS;
    }
}
