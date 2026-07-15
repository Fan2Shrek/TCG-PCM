<?php

namespace App\Game\Card\Character;

use App\Enum\GameEventTypeEnum;
use App\Game\Card\AbstractPassiveCard;
use App\Game\Card\Interface\TurnAwareInterface;
use App\Game\Card\Trait\TurnAwareTrait;
use App\Game\GameContext;
use App\Game\GameUtils;

final class CharlieCard extends AbstractCharacterCard implements TurnAwareInterface
{
    use TurnAwareTrait;

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
        return 150;
    }

    public function onTurnStart(GameContext $gameContext): void
    {
        if ($gameContext->isCurrentPlayer($this->getOwnerId())) {
            $randomPassiveCardId = $this->pickRandomPassiveCardId($gameContext);
            $newInstanceId = (string) $gameContext->state->randomizer->roll(0xFFFF_FFFF);

            $gameContext->pushGameEvent(GameEventTypeEnum::CARD_GENERATED, [
                'playerId' => $this->getOwnerId(),
                'cardTemplateId' => $randomPassiveCardId,
                'cardInstanceId' => $newInstanceId,
            ]);

            $gameContext->pushGameEvent(GameEventTypeEnum::CARD_PLAYED, [
                'playerId' => $this->getOwnerId(),
                'cardId' => $newInstanceId,
            ]);

            $gameContext->pushGameEvent(GameEventTypeEnum::CARD_PLACE_IN_PLAY_AREA, [
                'playerId' => $this->getOwnerId(),
                'cardId' => $newInstanceId,
            ]);
        }
    }

    private function pickRandomPassiveCardId(GameContext $context): string
    {
        $pool = $this->getPassiveTemplatePool();

        if ([] === $pool) {
            throw new \LogicException('Chaos template pool cannot be empty');
        }

        return $pool[$context->state->randomizer->randomBetweenInt(0, count($pool) - 1)];
    }

    /**
     * @return string[]
     */
    private function getPassiveTemplatePool(): array
    {
        return $this->passiveTemplatePool ??= GameUtils::getService('cards')->getAllBy([
            'type' => AbstractPassiveCard::class,
        ]);
    }
}
