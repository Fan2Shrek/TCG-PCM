<?php

namespace App\Game\Card;

use App\Enum\CardRarityEnum;
use App\Game\Card\Interface\TurnAwareInterface;
use App\Game\Card\Trait\BaseOnTurnTrait;
use App\Game\GameContext;
use App\Game\GameUtils;

final class StackyStackitoCard extends AbstractPassiveCard implements TurnAwareInterface
{
    use BaseOnTurnTrait;

    public static CardRarityEnum $rarity = CardRarityEnum::UNCOMMON;

    private const DELAY = 5;

    private int $delay = self::DELAY;

    public function getId(): string
    {
        return 'StackyStackito';
    }

    public function getDescription(): string
    {
        return GameUtils::formatDescription(parent::getDescription(), [
            'value' => $this->getTurnDelay(),
        ]);
    }

    private function onTurnAction(GameContext $gameContext): void
    {
        $damage = $gameContext->state->getPlayer($this->getOwnerId())->coins;
        $gameContext->attack($damage);
    }

    public function getTurnDelay(): int
    {
        return $this->getValue($this->delay, true);
    }
}
