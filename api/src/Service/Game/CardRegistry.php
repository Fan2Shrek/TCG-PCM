<?php

declare(strict_types=1);

namespace App\Service\Game;

use App\Enum\CardRarityEnum;
use App\Enum\CardSerieEnum;
use App\Game\AbstractCard;

class CardRegistry implements CardRegistryInterface
{
    /**
     * @var array<string, class-string<AbstractCard>>
     */
    private array $cards = [];

    /**
     * @var array<string, AbstractCard>
     */
    private array $instanciedCards = [];

    public function __construct(
        private string $cardsListPath,
    ) {}

    public function getCardTemplateById(string $cardId): AbstractCard
    {
        return $this->get($cardId);
    }

    public function getAllBy(array $criteria): array
    {
        $rarity = $criteria['rarity'] ?? null;
        $serie = $criteria['serie'] ?? null;

        if (null !== $rarity && !$rarity instanceof CardRarityEnum) {
            throw new \InvalidArgumentException(sprintf('Rarity must be an instance of %s', CardRarityEnum::class));
        }

        if (null !== $serie && !$serie instanceof CardSerieEnum) {
            throw new \InvalidArgumentException(sprintf('Serie must be an instance of %s', CardSerieEnum::class));
        }

        $this->loadCards();

        $cards = [];
        foreach ($this->cards as $cardId => $cardClass) {
            if ($rarity && $cardClass::$rarity !== $rarity) {
                continue;
            }

            if ($serie && $cardClass::$serie !== $serie) {
                continue;
            }

            $cards[] = $cardId;
        }

        return $cards;
    }

    /**
     * @return array<string, class-string<AbstractCard>>
     */
    protected function getCardsList(): array
    {
        return require $this->cardsListPath;
    }

    private function get(string $cardId): AbstractCard
    {
        return $this->instanciedCards[$cardId] ??= $this->createCardInstance($cardId);
    }

    private function createCardInstance(string $cardId): AbstractCard
    {
        if (!($this->cards[$cardId] ?? null)) {
            $this->loadCards();
        }

        if (!($class = $this->cards[$cardId] ?? null)) {
            throw new \RuntimeException(\sprintf('Card with id "%s" not found', $cardId));
        }

        return new $class();
    }

    private function loadCards(): void
    {
        if ([] !== $this->cards) {
            return;
        }

        $this->cards = $this->getCardsList();
    }
}
