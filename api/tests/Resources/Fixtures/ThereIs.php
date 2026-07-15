<?php

declare(strict_types=1);

namespace App\Tests\Resources\Fixtures;

use App\Tests\Resources\Fixtures\Builder\DeckBuilder;
use App\Tests\Resources\Fixtures\Builder\FriendshipBuilder;
use App\Tests\Resources\Fixtures\Builder\GameBuilder;
use App\Tests\Resources\Fixtures\Builder\Inventory\CardInventoryBuilder;
use App\Tests\Resources\Fixtures\Builder\Inventory\InventoryBuilder;
use App\Tests\Resources\Fixtures\Builder\RoomBuilder;
use App\Tests\Resources\Fixtures\Builder\TradeBuilder;
use App\Tests\Resources\Fixtures\Builder\UserBuilder;
use App\Tests\Resources\Fixtures\Builder\UserInfoBuilder;
use App\Tests\Resources\Fixtures\Builder\UserWalletBuilder;
use Psr\Container\ContainerInterface;

abstract /* static */ class ThereIs
{
    private static ContainerInterface $container;

    public static function aCardInventory(): CardInventoryBuilder
    {
        return new CardInventoryBuilder(self::$container);
    }

    public static function aDeck(): DeckBuilder
    {
        return new DeckBuilder(self::$container);
    }

    public static function aFriendship(): FriendshipBuilder
    {
        return new FriendshipBuilder(self::$container);
    }

    public static function aGame(): GameBuilder
    {
        return new GameBuilder(self::$container);
    }

    public static function anInventory(): InventoryBuilder
    {
        return new InventoryBuilder(self::$container);
    }

    public static function aRoom(): RoomBuilder
    {
        return new RoomBuilder(self::$container);
    }

    public static function aTrade(): TradeBuilder
    {
        return new TradeBuilder(self::$container);
    }

    public static function anUser(): UserBuilder
    {
        return new UserBuilder(self::$container);
    }

    public static function aUserInfo(): UserInfoBuilder
    {
        return new UserInfoBuilder(self::$container);
    }

    public static function aUserWallet(): UserWalletBuilder
    {
        return new UserWalletBuilder(self::$container);
    }

    public static function setContainer(ContainerInterface $container): void
    {
        self::$container = $container;
    }
}
