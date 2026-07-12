<?php

declare(strict_types=1);

namespace App\Service\Booster;

use App\Domain\Model\Booster;
use App\Enum\CardRarityEnum;
use App\Service\Booster\Types\BoosterInterface;
use App\Service\Game\CardRegistryInterface;

class BoosterGenerator
{
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

    public function __construct(
        private CardRegistryInterface $cardRegistry,
        private BoosterRegistry $boosterRegistry,
    ) {}

    public function generateBooster(?string $boosterType = null): Booster
    {
        $boosterType ??= 'default';
        $boosterClass = $this->boosterRegistry->getBoosterType($boosterType);

        if (
            !class_exists($boosterClass)
            || interface_exists($boosterClass)
            || !is_subclass_of($boosterClass, BoosterInterface::class, true)
        ) {
            throw new \InvalidArgumentException(\sprintf('Booster type "%s" must implement BoosterInterface.', $boosterType));
        }

        /** @var BoosterInterface $booster */
        $booster = new $boosterClass();
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
