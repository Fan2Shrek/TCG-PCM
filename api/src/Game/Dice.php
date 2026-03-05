<?php

declare(strict_types=1);

namespace App\Game;

abstract class Dice
{
    private static ?\Closure $generator = null;

    public static function setGenerator(?\Closure $generator): void
    {
        self::$generator = $generator;
    }

    public static function roll(int $sides): int
    {
        if (self::$generator === null) {
            self::$generator = static fn(int $sides): int => random_int(1, $sides);
        }

        return (int) (self::$generator)($sides);
    }

    public static function randomBetweenFloat(float $min, float $max): float
    {
        $min = (int) ($min * 100);
        $max = (int) ($max * 100);

        $value = self::roll($max - $min + 1) + $min - 1;

        return (float) $value / 100;
    }
}
