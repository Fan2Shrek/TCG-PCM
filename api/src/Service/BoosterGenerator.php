<?php

declare(strict_types=1);

namespace App\Service;

use App\Domain\Model\Booster;
use App\Enum\CardRarityEnum;
use App\Enum\CardSerieEnum;
use App\Service\Game\CardRegistryInterface;

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

    /**
     * @var array<string, string[]>
     */
    private array $cardCache = [];

    public function __construct(
        private CardRegistryInterface $cardRegistry,
    ) {}

    public function generateBooster(?CardSerieEnum $serie = null): Booster
    {
        $boosterCards = [];

        for ($i = 0; $i < static::BOOSTER_SIZE; $i++) {
            $rarity = $this->getRandomRarity();
            $availableCards = $this->getForRarityAndSerie($rarity, $serie);
            if ([] === $availableCards) {
                $i--;
                continue;
            }

            $randomCard = $availableCards[array_rand($availableCards)];
            $boosterCards[] = $randomCard;
        }

        return new Booster(array_map($this->cardRegistry->getCardTemplateById(...), $boosterCards));
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

    private function getForRarityAndSerie(CardRarityEnum $rarity, ?CardSerieEnum $serie): array
    {
        $cacheKey = \sprintf('%s_%s', $rarity->value, $serie->value ?? 'any');

        return $this->cardCache[$cacheKey] ??= $this->cardRegistry->getAllBy([
            'rarity' => $rarity,
            'serie' => $serie,
        ]);
    }
}
