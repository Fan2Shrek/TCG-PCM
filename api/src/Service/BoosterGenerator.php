<?php

declare(strict_types=1);

namespace App\Service;

use App\Domain\Model\Booster;
use App\Enum\CardRarityEnum;
use App\Game\AbstractCard;

// @todo add tests
final class BoosterGenerator
{
    // @todo change when there is engough cards
    private const BOOSTER_SIZE = 1;
    private const RARITY_PROBABILITIES = [
        CardRarityEnum::COMMON->value => 0.7,
        CardRarityEnum::UNCOMMON->value => 0.2,
        CardRarityEnum::RARE->value => 0.08,
        CardRarityEnum::EPIC->value => 0.015,
        CardRarityEnum::LEGENDARY->value => 0.005,
    ];

    /** @var class-string<AbstractCard>[] */
    private array $cards = [];

    public function __construct(
        private string $cardsListPath,
    ) {
    }

    public function generateBooster(): Booster
    {
        $this->loadCards();

        $boosterCards = [];

        for ($i = 0; $i < self::BOOSTER_SIZE; $i++) {
            $rarity = $this->getRandomRarity();
            $availableCards = array_filter($this->cards, fn(string $card) => $card::$rarity === $rarity);

            if (empty($availableCards)) {
                $i--;
                continue;
            }

            $randomCard = $availableCards[array_rand($availableCards)];
            $boosterCards[] = new $randomCard();
        }

        return new Booster($boosterCards);
    }

    private function getRandomRarity(): CardRarityEnum
    {
        $rand = mt_rand() / mt_getrandmax();
        $cumulativeProbability = 0.0;

        foreach (self::RARITY_PROBABILITIES as $rarity => $probability) {
            $cumulativeProbability += $probability;
            if ($rand <= $cumulativeProbability) {
                return CardRarityEnum::from($rarity);
            }
        }

        return CardRarityEnum::COMMON;
    }

    private function loadCards(): void
    {
        if (!empty($this->cards)) {
            return;
        }

        $this->cards = require $this->cardsListPath;
    }
}
