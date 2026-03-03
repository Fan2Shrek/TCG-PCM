<?php

declare(strict_types=1);

namespace App\Game\State;

final readonly class PlayArea
{
    /**
     * @param string[] $passiveCards
     */
    public function __construct(
        public array $passiveCards = [],
    ) {}

    public function addPassiveCard(string $cardId): self
    {
        return clone($this, [
            'passiveCards' => [...$this->passiveCards, $cardId],
        ]);
    }

    /**
     * @return string[]
     */
    public function getAll(): array
    {
        return array_merge($this->passiveCards);
    }
}
