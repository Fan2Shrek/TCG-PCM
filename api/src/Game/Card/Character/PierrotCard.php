<?php

declare(strict_types=1);

namespace App\Game\Card\Character;

use App\Enum\CardEffectEnum;
use App\Game\Card\Interface\TurnAwareInterface;
use App\Game\Card\Trait\BaseOnTurnTrait;
use App\Game\GameContext;
use App\Game\GameUtils;

final class PierrotCard extends AbstractCharacterCard implements TurnAwareInterface
{
    use BaseOnTurnTrait;

    private const TURN_DELAY = 2;

    public function getId(): string
    {
        return 'Pierrot';
    }

    public function getHealthPoints(): int
    {
        return 175;
    }

    public function onTurnAction(GameContext $gameContext): void
    {
        $cardId = $gameContext->getOneRandomCard($gameContext->getOtherPlayerId($this->ownerId));

        $gameContext->addEffect(CardEffectEnum::TORNED, $cardId);
    }

    public function getDescription(): string
    {
        return GameUtils::formatDescription(parent::getDescription(), [
            'effect' => CardEffectEnum::TORNED,
            'value1' => 1,
            'value2' => $this->getTurnDelay(),
        ]);
    }

    public function getTurnDelay(): int
    {
        return $this->getValue(self::TURN_DELAY, true);
    }
}
