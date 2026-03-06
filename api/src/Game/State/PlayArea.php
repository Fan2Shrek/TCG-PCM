<?php

declare(strict_types=1);

namespace App\Game\State;

final readonly class PlayArea
{
    /**
     * @param string[] $passiveCards
     * @param string[] $monsterCards
     */
    public function __construct(
        public array $passiveCards = [],
        public array $monsterCards = [],
    ) {}

    public function addPassiveCard(string $cardId): self
    {
        return clone($this, [
            'passiveCards' => [...$this->passiveCards, $cardId],
        ]);
    }

    public function addMonsterCard(string $cardId): self
    {
        return clone($this, [
            'monsterCards' => [$cardId],
        ]);
    }

    /**
     * @return string[]
     */
    public function getAll(): array
    {
        return array_merge($this->passiveCards, $this->monsterCards);
    }
}
