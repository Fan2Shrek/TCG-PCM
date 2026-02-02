<?php

declare(strict_types=1);

namespace App\Game;

use Symfony\Component\Serializer\Attribute\Ignore;

final class Player
{
    /**
     * @param AbstractCard[] $hand
     * @param AbstractCard[] $deck
     */
    public function __construct(
        public readonly string $name,
        public readonly int $healthPoints,
        private array $hand = [],
        private array $deck = [],
    ) {}

    public function drawCard(int $count = 1): void
    {
        for ($i = 0; $i < $count; $i++) {
            if ([] === $this->deck) {
                break;
            }

            $card = array_shift($this->deck);
            $this->hand[] = $card;
        }
    }

    #[Ignore]
    public function getHandSize(): int
    {
        return count($this->hand);
    }
}
