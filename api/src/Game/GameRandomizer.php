<?php

declare(strict_types=1);

namespace App\Game;

use Random\Engine\Mt19937;
use Random\Randomizer;

class GameRandomizer
{
    private Randomizer $randomizer;

    public function __construct(
        private ?int $seed,
    ) {
        $this->randomizer = new Randomizer(new Mt19937($seed));
    }

    public function roll(int $sides): int
    {
        return $this->_doRandom(1, $sides);
    }

    public function randomBetweenFloat(float $min, float $max): float
    {
        $min = (int) ($min * 100);
        $max = (int) ($max * 100);

        $value = self::_doRandom($min, $max);

        return (float) $value / 100;
    }

    private function _doRandom(int $min, int $max): int
    {
        return (int) $this->randomizer->getInt($min, $max);
    }

    public function __serialize(): array
    {
        return [
            'seed' => $this->seed,
            'randomizer' => $this->randomizer,
        ];
    }
}
