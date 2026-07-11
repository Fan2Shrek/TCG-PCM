<?php

declare(strict_types=1);

namespace App\Game\Card;

use App\Enum\CardSetEnum;
use App\Game\GameContext;
use App\Game\GameUtils;

final class BananaCard extends AbstractPlayableCard
{
    public static CardSetEnum $serie = CardSetEnum::BTD6;

    private const HEAL_AMOUNT = 5;

    public function getId(): string
    {
        return 'Banana';
    }

    public function getDescription(): string
    {
        return GameUtils::formatDescription(parent::getDescription(), [
            'value' => $this->getValue(self::HEAL_AMOUNT, true),
        ]);
    }

    public function play(GameContext $context, array $data = []): void
    {
        $ownerId = $this->getOwnerId();
        if (null === $ownerId) {
            return;
        }

        $context->heal($this->getValue(self::HEAL_AMOUNT, true), $ownerId);
    }
}
