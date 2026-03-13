<?php

declare(strict_types=1);

namespace App\DependencyInjection;

use App\Service\Game\State\RedisGameStateRepository;
use App\Service\Redis\RedisClient;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class UseRedisGameStateRepositoryPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($container->resolveEnvPlaceholders('%env(USE_REDIS)%', true)) {
            $definition = $container->getDefinition('game.game_state_repository');
            $container
                ->register('game.game_state_repository.redis', RedisGameStateRepository::class)
                ->setDecoratedService('game.game_state_repository')
                ->setArguments([
                    new Reference(RedisClient::class),
                    $definition,
                    $container->getDefinition('game.game_event_repository'),
                    $container->getDefinition('game.game_state_rebuilder'),
                ]);
        }
    }
}
