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

    public function hasMonsterCard(string $id): bool
    {
        return \in_array($id, $this->monsterCards, true);
    }

    /**
     * @return string[]
     */
    public function getAll(): array
    {
        return array_merge($this->passiveCards, $this->monsterCards);
    }

    #[\NoDiscard]
    public function addPassiveCard(string $cardId): self
    {
        return clone($this, [
            'passiveCards' => [...$this->passiveCards, $cardId],
        ]);
    }

    #[\NoDiscard]
    public function addMonsterCard(string $cardId): self
    {
        return clone($this, [
            'monsterCards' => [$cardId],
        ]);
    }

    #[\NoDiscard]
    public function removePassiveCard(string $cardId): self
    {
        return clone($this, [
            'passiveCards' => array_filter($this->passiveCards, static fn(string $id) => $id !== $cardId),
        ]);
    }

    #[\NoDiscard]
    public function removeMonsterCard(string $cardId): self
    {
        return clone($this, [
            'monsterCards' => array_filter($this->monsterCards, static fn(string $id) => $id !== $cardId),
        ]);
    }
}
