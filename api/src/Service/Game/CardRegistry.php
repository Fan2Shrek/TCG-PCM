<?php

declare(strict_types=1);

namespace App\Service\Game;

use App\Enum\CardRarityEnum;
use App\Game\AbstractCard;

class CardRegistry
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

    public function getCardInstanceById(string $cardId): AbstractCard
    {
        return $this->get($cardId);
    }

    public function getAllByRarity(CardRarityEnum $rarity): array
    {
        $this->loadCards();

        $cards = [];
        foreach ($this->cards as $cardId => $cardClass) {
            if ($cardClass::$rarity !== $rarity) {
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
        if (!isset($this->cards[$cardId])) {
            $this->loadCards();
        }

        if (!isset($this->cards[$cardId])) {
            throw new \RuntimeException(\sprintf('Card with id "%s" not found', $cardId));
        }

        $class = $this->cards[$cardId];

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
