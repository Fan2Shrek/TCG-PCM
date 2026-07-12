<?php

namespace App\Game\Card\Monster;

use App\Enum\CardRarityEnum;
use App\Enum\GameEventTypeEnum;
use App\Game\Card\CardState;
use App\Game\GameContext;
use App\Game\GameUtils;

class MimeticPrismRubyCard extends AbstractMonsterCard
{
    public static CardRarityEnum $rarity = CardRarityEnum::RARE;

    protected const HEALTH_POINTS_MULTIPLIER = 0.5;
    protected const ATTACK_MULTIPLIER = 2;

    private string $copyTemplateId;
    private int $damage;
    private int $heal;

    public function getId(): string
    {
        return 'MimeticPrismRuby';
    }

    public function setState(CardState $state): void
    {
        if (\is_string($state->values['templateId'] ?? null)) {
            $this->copyTemplateId = $state->values['templateId'];
            $this->getMimedCard();
        }

        parent::setState($state);
    }

    public function getBaseAttack(): int
    {
        return $this->damage ?? 0;
    }

    public function getHealPoints(): int
    {
        return $this->heal ?? 0;
    }

    public function onMonsterPlayed(GameContext $context): void
    {
        $pool = $context->getPlayerStateById($this->ownerId)->playArea->monsterCards;

        if ([] === $pool) {
            return;
        }

        $copyId = $context->selectRandomCardIn($pool);
        $state = $context->state->getCardState($copyId);

        if (!$state) {
            throw new \LogicException(sprintf('Card state with id "%s" not found', $copyId));
        }

        $this->copyTemplateId = $state->templateId;

        $context->pushGameEvent(GameEventTypeEnum::UPDATE_CARD_STATE, [
            'cardId' => $this->getInstanceId(),
            'stateToUpdate' => [
                'templateId' => $this->copyTemplateId,
            ],
        ]);
    }

    protected function getMimedCard(): void
    {
        /** @var AbstractMonsterCard $card */
        $card = GameUtils::getService('card')->getCardTemplateById($this->copyTemplateId);

        $this->damage = (int) round($card->getBaseAttack() * self::ATTACK_MULTIPLIER);
        $this->heal = (int) round($card->getHealPoints() * self::HEALTH_POINTS_MULTIPLIER);
    }
}
