<?php

declare(strict_types=1);

namespace App\Tests\Unit\DependencyInjection;

use App\DependencyInjection\UseRedisGameStateRepositoryPass;
use App\Service\Game\State\RedisGameStateRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class UseRedisGameStateRepositoryPassTest extends TestCase
{
    public function tearDown(): void
    {
        $_ENV['USE_REDIS'] = '0';
    }

    public function testProcess(): void
    {
        $container = new ContainerBuilder();
        $_ENV['USE_REDIS'] = '1';
        $container->register('game.game_state_repository', 'stdClass');
        $container->register('game.game_event_repository', 'stdClass');
        $container->register('game.game_state_rebuilder', 'stdClass');

        new UseRedisGameStateRepositoryPass()->process($container);

        self::assertTrue($container->has('game.game_state_repository.redis'));
        $definition = $container->getDefinition('game.game_state_repository.redis');
        self::assertSame(RedisGameStateRepository::class, $definition->getClass());
    }
}
