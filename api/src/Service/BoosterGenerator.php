<?php

declare(strict_types=1);

namespace App\Service;

use App\Domain\Model\Booster;
use App\Enum\CardRarityEnum;
use App\Game\AbstractCard;

class BoosterGenerator
{
    protected const BOOSTER_SIZE = 1;

    /**
     * @var array<string, float>
     */
    protected const RARITY_PROBABILITIES = [
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

        for ($i = 0; $i < static::BOOSTER_SIZE; $i++) {
            $rarity = $this->getRandomRarity();
            $availableCards = array_filter(
                $this->cards,
                fn(string $card) => $card::$rarity === $rarity && !\in_array($card, $boosterCards, true)
            );

            if (empty($availableCards)) {
                $i--;
                continue;
            }

            $randomCard = $availableCards[array_rand($availableCards)];
            $boosterCards[] = $randomCard;
        }

        return new Booster(array_map(fn (string $cardClass) => new $cardClass(), $boosterCards));
    }

    protected function getRandomRarity(): CardRarityEnum
    {
        $rand = mt_rand() / mt_getrandmax();
        $cumulativeProbability = 0.0;

        foreach (static::RARITY_PROBABILITIES as $rarity => $probability) {
            $cumulativeProbability += $probability;
            if ($rand <= $cumulativeProbability) {
                return CardRarityEnum::from($rarity);
            }
        }

        return CardRarityEnum::COMMON;
    }

    /**
    * @return class-string<AbstractCard>[]
    */
    protected function getCardsList(): array
    {
        return require $this->cardsListPath;
    }

    private function loadCards(): void
    {
        if (!empty($this->cards)) {
            return;
        }

        $this->cards = $this->getCardsList();
    }
}
