<?php

declare(strict_types=1);

namespace App\Game\Card;

use App\Enum\CardRarityEnum;
use App\Game\Card\Interface\TurnAwareInterface;
use App\Game\Card\Trait\TurnAwareTrait;
use App\Game\GameContext;

final class PlacentaCard extends AbstractPassiveCard implements TurnAwareInterface
{
    use TurnAwareTrait;

    public const CardRarityEnum RARITY = CardRarityEnum::UNCOMMON;

    private const HEALTH_GAIN = 5;

    public function getName(): string
    {
        return 'Placenta';
    }

    public function getId(): string
    {
        return 'PLacenta';
    }

    public function getDescription(): string
    {
        return 'At the beginning of each turn, gain <value>5</value> health.';
    }

    public function onTurnStart(GameContext $gameContext): void {}
}
