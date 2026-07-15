<?php

declare(strict_types=1);

namespace App\Game\Card\Monster;

use App\Enum\CardRarityEnum;
use App\Enum\CardSetEnum;
use App\Enum\GameEventTypeEnum;
use App\Game\GameContext;
use App\Game\GameUtils;

final class MegaClottyCard extends AbstractMonsterCard
{
    public static CardRarityEnum $rarity = CardRarityEnum::RARE;
    public static CardSetEnum $serie = CardSetEnum::TBOI;

    private const HEALTH_POINTS = 15;
    private const ATTACK = 15;
    private const BONUS_PER_CLOTTY = 7;
    private const NUMBER_OF_CLOTTIES_SPAWNED = 2;

    private const CLOTTY_IDS = [
        'Clotty',
        'MegaClotty',
        'GrilledClotty'
    ];

    private int $playBonus = 0;

    public function getId(): string
    {
        return 'MegaClotty';
    }

    public function getDescription(): string
    {
        return GameUtils::formatDescription(parent::getDescription(), [
            'value' => $this->getValue(self::BONUS_PER_CLOTTY, true),
            'value2' => $this->getValue(self::BONUS_PER_CLOTTY, true),
            'value3' => $this->getValue(self::NUMBER_OF_CLOTTIES_SPAWNED, true),
        ]);
    }

    public function getBaseAttack(): int
    {
        return self::ATTACK + $this->playBonus;
    }

    public function getHealPoints(): int
    {
        return self::HEALTH_POINTS + $this->playBonus;
    }

    public function onMonsterPlayed(GameContext $context): void
    {
        $ownerId = $this->getOwnerId();
        $instanceId = $this->getInstanceId();

        if (null === $ownerId || null === $instanceId) {
            return;
        }

        $clottiesOnBoard = 0;

        foreach ($this->getAllActivePlayAreaCards($context) as $cardId) {
            $templateId = $context->state->getCardState($cardId)?->templateId;

            if (\is_string($templateId) && \in_array($templateId, self::CLOTTY_IDS, true)) {
                $clottiesOnBoard++;
                $context->discardCard($cardId);
            }
        }

        $this->playBonus = $this->getValue($clottiesOnBoard * self::BONUS_PER_CLOTTY, true);

        $context->pushGameEvent(GameEventTypeEnum::UPDATE_CARD_STATE, [
            'cardId' => $instanceId,
            'stateToUpdate' => [
                'bonusAttack' => $this->playBonus,
                'bonusHealth' => $this->playBonus,
            ],
        ]);
    }

    /**
     * @return string[]
     */
    private function getAllActivePlayAreaCards(GameContext $context): array
    {
        return array_merge($context->state->player1->playArea->getAll(), $context->state->player2->playArea->getAll());
    }

    public function onMonsterDeath(GameContext $gameContext): void
    {
        for ($i = 0; $i < self::NUMBER_OF_CLOTTIES_SPAWNED; $i++) {
            $newInstanceId = (string) $gameContext->state->randomizer->roll(0xFFFF_FFFF);
            $cardTemplateId = "Clotty";

            $gameContext->pushGameEvent(GameEventTypeEnum::CARD_GENERATED, [
                'playerId' => $this->getOwnerId(),
                'cardTemplateId' => $cardTemplateId,
                'cardInstanceId' => $newInstanceId,
            ]);

            $gameContext->pushGameEvent(GameEventTypeEnum::CARD_PLACE_IN_PLAY_AREA, [
                'playerId' => $this->getOwnerId(),
                'cardId' => $newInstanceId,
            ]);
        }
    }
}
