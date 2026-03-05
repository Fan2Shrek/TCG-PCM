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
            self::$generator = random_int(...);
        }

        return (int) (self::$generator)($min, $max);
    }
}
