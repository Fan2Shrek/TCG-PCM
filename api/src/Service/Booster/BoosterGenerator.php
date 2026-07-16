<?php

declare(strict_types=1);

namespace App\Service\Booster;

use App\Domain\Model\Booster;
use App\Enum\CardRarityEnum;
use App\Service\Game\CardRegistryInterface;

class BoosterGenerator
{
    /**
     * @var array<string, float>
     */
    protected const RARITY_PROBABILITIES = [
        CardRarityEnum::COMMON->value => 0.69,
        CardRarityEnum::UNCOMMON->value => 0.20,
        CardRarityEnum::RARE->value => 0.085,
        CardRarityEnum::EPIC->value => 0.016,
        CardRarityEnum::LEGENDARY->value => 0.009,
    ];

    public function __construct(
        private CardRegistryInterface $cardRegistry,
        private BoosterRegistry $boosterRegistry,
    ) {}

    public function generateBooster(?string $boosterType = null): Booster
    {
        $boosterType ??= 'default';
        $booster = $this->boosterRegistry->createBooster($boosterType);
        $boosterCards = [];

        for ($i = 0; $i < $booster->getCapacity(); $i++) {
            $rarity = $this->getRandomRarity();
            $criterias = $booster->getCardsCriteria();
            $availableCards = $this->getCardsFromCriteria(array_merge($criterias, ['rarity' => $rarity]));
            if ([] === $availableCards) {
                $i--;
                continue;
            }

            $randomCard = $availableCards[array_rand($availableCards)];

            if (\in_array($randomCard, $boosterCards, true)) {
                $i--;
                continue;
            }

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

    /**
     * @param array<string, mixed> $criteria
     */
    private function getCardsFromCriteria(array $criteria): array
    {
        return $this->cardRegistry->getAllBy($criteria);
    }
}
