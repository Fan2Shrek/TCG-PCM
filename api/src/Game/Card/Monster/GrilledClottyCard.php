<?php

declare(strict_types=1);

namespace App\Game\Card\Monster;

use App\Enum\CardRarityEnum;
use App\Enum\CardSetEnum;
use App\Game\Card\Interface\TurnAwareInterface;
use App\Game\Card\Trait\TurnAwareTrait;
use App\Game\GameContext;

final class GrilledClottyCard extends AbstractMonsterCard implements TurnAwareInterface
{
    use TurnAwareTrait;

    public static CardRarityEnum $rarity = CardRarityEnum::UNCOMMON;
    public static CardSetEnum $serie = CardSetEnum::TBOI;

    private const HEALTH_POINTS = 5;
    private const ATTACK = 25;
    private const SELF_DAMAGE = 1;

    public function getId(): string
    {
        return 'GrilledClotty';
    }

    public function getBaseAttack(): int
    {
        return self::ATTACK;
    }

    public function getHealPoints(): int
    {
        return self::HEALTH_POINTS;
    }

    public function onTurnEnd(GameContext $gameContext): void
    {
        $instanceId = $this->getInstanceId();
        if (null === $instanceId) {
            return;
        }

        $gameContext->damageCard($instanceId, self::SELF_DAMAGE);
    }
}
