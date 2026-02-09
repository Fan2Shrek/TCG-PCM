<?php

declare(strict_types=1);

namespace App\Tests\Resources\Fixtures;

use App\Tests\Resources\Fixtures\Builder\DeckBuilder;
use App\Tests\Resources\Fixtures\Builder\GameBuilder;
use App\Tests\Resources\Fixtures\Builder\RoomBuilder;
use App\Tests\Resources\Fixtures\Builder\UserBuilder;
use Psr\Container\ContainerInterface;

abstract /* static */ class ThereIs
{
    private static ContainerInterface $container;

    public static function aDeck(): DeckBuilder
    {
        return new DeckBuilder(self::$container);
    }

    public static function aGame(): GameBuilder
    {
        return new GameBuilder(self::$container);
    }

    public static function aRoom(): RoomBuilder
    {
        return new RoomBuilder(self::$container);
    }

    public static function anUser(): UserBuilder
    {
        return new UserBuilder(self::$container);
    }

    public static function setContainer(ContainerInterface $container): void
    {
        self::$container = $container;
    }
}
