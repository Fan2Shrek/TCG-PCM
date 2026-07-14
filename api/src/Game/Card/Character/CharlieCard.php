<?php

namespace App\Game\Card\Character;

use App\Enum\GameEventTypeEnum;
use App\Game\Card\AbstractPassiveCard;
use App\Game\Card\Interface\TurnAwareInterface;
use App\Game\Card\Trait\TurnAwareTrait;
use App\Game\GameContext;

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

    public function onTurnStart(GameContext $context): void
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
            throw new \LogicException('Chaos template pool cannot be empty');
        }

        return $pool[$context->state->randomizer->randomBetweenInt(0, count($pool) - 1)];
    }

    /**
     * @return string[]
     */
    private function getPassiveTemplatePool(): array
    {
        if (null !== self::$passiveTemplatePool) {
            return self::$passiveTemplatePool;
        }

        $pool = [];
        foreach ($this->getCardsList() as $templateId => $cardClass) {
            $card = new $cardClass();
            if ($card instanceof AbstractPassiveCard) {
                $pool[] = $templateId;
            }
        }

        self::$passiveTemplatePool = $pool;

        return $pool;
    }

    /**
     * @return array<string, class-string<\App\Game\AbstractCard>>
     */
    private function getCardsList(): array
    {
        /** @var array<string, class-string<\App\Game\AbstractCard>> $cardsList */
        $cardsList = require __DIR__.'/../../../../resources/cards_list.php';
        return $cardsList;
    }
}
