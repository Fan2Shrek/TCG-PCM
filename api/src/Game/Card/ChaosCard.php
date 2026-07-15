<?php

declare(strict_types=1);

namespace App\Game\Card;

use App\Enum\CardRarityEnum;
use App\Enum\GameEventTypeEnum;
use App\Game\Card\Monster\AbstractMonsterCard;
use App\Game\GameContext;
use App\Game\GameUtils;

final class ChaosCard extends AbstractPlayableCard
{
    public static CardRarityEnum $rarity = CardRarityEnum::RARE;

    /**
     * @var string[]|null
     */
    private static ?array $monsterTemplatePool = null;

    /**
     * @var string[]|null
     */
    private static ?array $passiveTemplatePool = null;

    public function getId(): string
    {
        return 'Chaos';
    }

    public function play(GameContext $context, array $data = []): void
    {
        $cardsInPlay = array_merge($context->state->player1->playArea->getAll(), $context->state->player2->playArea->getAll());

        if ([] === $cardsInPlay) {
            return;
        }

        foreach ($cardsInPlay as $cardId) {
            $cardState = $context->state->getCardState($cardId);
            if (null === $cardState) {
                continue;
            }

            $ownerState = $context->state->getPlayer($cardState->ownerId);
            $isMonster = \in_array($cardId, $ownerState->playArea->monsterCards, true);
            $replacementTemplateId = $this->pickRandomTemplateId($context, $isMonster);
            $newInstanceId = (string) $context->state->randomizer->roll(0xFFFF_FFFF);

            $context->discardCard($cardId);

            $context->pushGameEvent(GameEventTypeEnum::CARD_GENERATED, [
                'playerId' => $cardState->ownerId,
                'cardTemplateId' => $replacementTemplateId,
                'cardInstanceId' => $newInstanceId,
            ]);

            $context->pushGameEvent(GameEventTypeEnum::CARD_PLAYED, [
                'playerId' => $cardState->ownerId,
                'cardId' => $newInstanceId,
            ]);

            if ($isMonster) {
                $replacementMonster = GameUtils::getService('cards')->createCardInstance($replacementTemplateId);
                dump($replacementMonster);

                if (!$replacementMonster instanceof AbstractMonsterCard) {
                    throw new \LogicException('Chaos picked a non-monster template for a monster slot');
                }

                $context->pushGameEvent(GameEventTypeEnum::CARD_PLACE_IN_MONSTER_AREA, [
                    'playerId' => $cardState->ownerId,
                    'cardId' => $newInstanceId,
                    'cardHealthPoints' => $replacementMonster->getHealPoints(),
                ]);

                continue;
            }

            $context->pushGameEvent(GameEventTypeEnum::CARD_PLACE_IN_PLAY_AREA, [
                'playerId' => $cardState->ownerId,
                'cardId' => $newInstanceId,
            ]);
        }
    }

    private function pickRandomTemplateId(GameContext $context, bool $monster): string
    {
        $pool = $monster ? $this->getMonsterTemplatePool() : $this->getPassiveTemplatePool();

        if ([] === $pool) {
            throw new \LogicException('Chaos template pool cannot be empty');
        }

        return $pool[$context->state->randomizer->randomBetweenInt(0, count($pool) - 1)];
    }

    /**
     * @return string[]
     */
    private function getMonsterTemplatePool(): array
    {
        return self::$monsterTemplatePool ??= GameUtils::getService('cards')->getCardsBy([
            'type' => AbstractMonsterCard::class,
        ]);
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
