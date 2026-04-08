<?php

declare(strict_types=1);

namespace App\Game\Card;

use App\Enum\CardSetEnum;
use App\Game\GameContext;
use App\Game\GameUtils;

final class SpicyD6Card extends AbstractPlayableCard
{
    public static CardSetEnum $serie = CardSetEnum::TBOI;

    private const DAMAGE_MULTIPLIER = 10;

    public function getId(): string
    {
        return 'Spicy-D6';
    }

    public function getImage(): string
    {
        return 'https://www.shutterstock.com/image-photo/red-die-on-white-six-260nw-27724336.jpg';
    }

    public function getDescription(): string
    {
        return GameUtils::formatDescription(parent::getDescription(), [
            'value' => $this->getValue(self::DAMAGE_MULTIPLIER, true),
        ]);
    }

    public function play(GameContext $context, array $data = []): void
    {
        $context->attack($context->rollDice(6) * $this->getValue(self::DAMAGE_MULTIPLIER, true));
    }
}
