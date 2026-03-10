<?php

declare(strict_types=1);

namespace App\Game;

use Random\Engine\Mt19937;
use Random\Randomizer;

abstract class GameRandomizer
{
    private static Randomizer $randomizer;

    private static ?\Closure $generator = null;

    public static function setUp(?int $seed): void
    {
        self::$randomizer = new Randomizer(new Mt19937($seed));
    }

    public static function setGenerator(?\Closure $generator): void
    {
        self::$generator = $generator;
    }

    public static function roll(int $sides): int
    {
        return self::_doRandom(1, $sides);
    }

    public static function randomBetweenFloat(float $min, float $max): float
    {
        $min = (int) ($min * 100);
        $max = (int) ($max * 100);

        $value = self::_doRandom($min, $max);

        return (float) $value / 100;
    }

    private static function _doRandom(int $min, int $max): int
    {
        if (self::$generator === null) {
            if (null === self::$randomizer ?? null) {
                self::setUp(null);
            }
            self::$generator = static fn(int $min, int $max) => self::$randomizer->getInt($min, $max);
        }

        return (int) (self::$generator)($min, $max);
    }
}
