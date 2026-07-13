<?php

declare(strict_types=1);

namespace App\Game\Card;

use App\Enum\CardRarityEnum;
use App\Enum\GameEventTypeEnum;
use App\Game\Card\Character\AbstractCharacterCard;
use App\Game\Card\Monster\AbstractMonsterCard;
use App\Game\GameContext;

final class ChaosCard extends AbstractPlayableCard
{
    public static CardRarityEnum $rarity = CardRarityEnum::RARE;

    /**
     * @var array<string, class-string<\App\Game\AbstractCard>>|null
     */
    private static ?array $cardsList = null;

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
                $replacementMonster = $this->instantiateCard($replacementTemplateId);

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
        if (null !== self::$monsterTemplatePool) {
            return self::$monsterTemplatePool;
        }

        $pool = [];
        foreach ($this->getCardsList() as $templateId => $cardClass) {
            $card = new $cardClass();
            if ($card instanceof AbstractMonsterCard) {
                $pool[] = $templateId;
            }
        }

        self::$monsterTemplatePool = $pool;

        return $pool;
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
        if (null === self::$cardsList) {
            /** @var array<string, class-string<\App\Game\AbstractCard>> $cardsList */
            $cardsList = require __DIR__.'/../../../resources/cards_list.php';
            self::$cardsList = $cardsList;
        }

        return self::$cardsList;
    }

    private function instantiateCard(string $templateId): \App\Game\AbstractCard
    {
        $cardsList = $this->getCardsList();
        $cardClass = $cardsList[$templateId] ?? null;

        if (null === $cardClass) {
            throw new \LogicException(sprintf('Unknown card template "%s"', $templateId));
        }

        return new $cardClass();
    }
}
