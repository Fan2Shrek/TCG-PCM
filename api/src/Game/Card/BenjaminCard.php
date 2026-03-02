<?php

declare(strict_types=1);

namespace App\Game\Card;

use App\Enum\CardEffectEnum;
use App\Game\GameContext;

final class BenjaminCard extends AbstractPlayableCard
{
    private const int CARD_COUNT = 1;

    public function getId(): string
    {
        return 'Benjamin';
    }

    public function getName(): string
    {
        return 'Benjamin';
    }

    public function getDescription(): string
    {
        return 'Add <effect>Hacked</effect> to <value>1</value> cards';
    }

    public function play(GameContext $context, array $data = []): void
    {
        for ($i = 0; $i < $this->getValue(self::CARD_COUNT, true); $i++) {
            if (!($card = $data['cards'][$i] ?? null)) {
                throw new \InvalidArgumentException('Missing card data for Benjamin card effect');
            }

            if (!\is_string($card)) {
                throw new \InvalidArgumentException('Invalid card data for Benjamin card effect');
            }

            $context->addEffect(CardEffectEnum::HACKED, $card);
        }
    }
}
