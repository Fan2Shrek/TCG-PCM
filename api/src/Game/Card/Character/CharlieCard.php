<?php

namespace App\Game\Card\Character;

use App\Enum\GameEventTypeEnum;
use App\Game\Card\AbstractPassiveCard;
use App\Game\Card\Interface\TurnAwareInterface;
use App\Game\Card\Trait\BaseOnTurnTrait;
use App\Game\GameContext;
use App\Game\GameUtils;

final class CharlieCard extends AbstractCharacterCard implements TurnAwareInterface
{
    use BaseOnTurnTrait;

    private const TURN_DELAY = 2;

    /**
     * @var string[]|null
     */
    private static ?array $passiveTemplatePool = null;

    public function getId(): string
    {
        return 'Charlie';
    }

    public function getHealthPoints(): int
    {
        return 125;
    }

    public function getTurnDelay(): int
    {
        return $this->getValue(self::TURN_DELAY, true);
    }

    public function getDescription(): string
    {
        return GameUtils::formatDescription(parent::getDescription(), [
            'value1' => 1,
            'value2' => $this->getTurnDelay(),
        ]);
    }

    public function onTurnAction(GameContext $context): void
    {
        if ($context->isCurrentPlayer($this->getOwnerId())) {
            $randomPassiveCardId = $this->pickRandomPassiveCardId($context);
            $newInstanceId = (string) $context->state->randomizer->roll(0xFFFF_FFFF);

            $context->pushGameEvent(GameEventTypeEnum::CARD_GENERATED, [
                'playerId' => $this->getOwnerId(),
                'cardTemplateId' => $randomPassiveCardId,
                'cardInstanceId' => $newInstanceId,
            ]);

            $context->pushGameEvent(GameEventTypeEnum::CARD_PLAYED, [
                'playerId' => $this->getOwnerId(),
                'cardId' => $newInstanceId,
            ]);

            $context->pushGameEvent(GameEventTypeEnum::CARD_PLACE_IN_PLAY_AREA, [
                'playerId' => $this->getOwnerId(),
                'cardId' => $newInstanceId,
            ]);
        }
    }

    private function pickRandomPassiveCardId(GameContext $context): string
    {
        $pool = $this->getPassiveTemplatePool();

        if ([] === $pool) {
            throw new \LogicException('Charlie template pool cannot be empty');
        }

        return $pool[$context->state->randomizer->randomBetweenInt(0, count($pool) - 1)];
    }

    /**
     * @return string[]
     */
    private function getPassiveTemplatePool(): array
    {
        return self::$passiveTemplatePool ??= GameUtils::getService('cards')->getCardsBy([
            'type' => AbstractPassiveCard::class,
        ]);
    }
}
