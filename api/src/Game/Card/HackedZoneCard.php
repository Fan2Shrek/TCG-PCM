<?php

declare(strict_types=1);

namespace App\Game\Card;

use App\Enum\CardEffectEnum;
use App\Enum\CardRarityEnum;
use App\Game\Card\Effect\HackedCardEffect;
use App\Game\Card\Interface\CardAwareInterface;
use App\Game\Card\Trait\CardAwareTrait;
use App\Game\GameContext;
use App\Game\GameUtils;

final class HackedZoneCard extends AbstractPassiveCard implements CardAwareInterface
{
    use CardAwareTrait;

    public static CardRarityEnum $rarity = CardRarityEnum::EPIC;

    public function getId(): string
    {
        return 'HackedZone';
    }

    public function getName(): string
    {
        return 'Hacked zone';
    }

    public function getDescription(): string
    {
        return GameUtils::formatDescription('Apply {{effect}} to all cards in both side', [
            'effect' => CardEffectEnum::HACKED,
        ]);
    }

    public function onCardPlace(GameContext $gameContext): void
    {
        $cards = array_merge(
            $gameContext->state->player1->hand,
            $gameContext->state->player2->hand,
            $gameContext->state->player1->playArea->getAll(),
            $gameContext->state->player2->playArea->getAll(),
            [
                $gameContext->state->player1->characterCardId,
                $gameContext->state->player2->characterCardId,
            ],
        );

        foreach ($cards as $card) {
            $gameContext->addEffect(CardEffectEnum::HACKED, $card, [
                'value' => $gameContext->randomBetween(HackedCardEffect::MIN_MODIFIER, HackedCardEffect::MAX_MODIFIER),
            ]);
        }
    }

    public function onCardDrawn(GameContext $gameContext): void
    {
        $this->beforeAction($gameContext);

        if ($gameContext->lastActionHasBeenPrevented()) {
            return;
        }

        if (!($id = $gameContext->state->getNextDrawId())) {
            throw new \LogicException('Card must have an instanceId to be affected by HackedZoneCard');
        }

        $gameContext->addEffect(CardEffectEnum::HACKED, $id, [
            'value' => $gameContext->randomBetween(HackedCardEffect::MIN_MODIFIER, HackedCardEffect::MAX_MODIFIER),
        ]);
    }
}
