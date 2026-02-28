<?php

namespace App;

use App\Debug\GameContext\TraceableGameContextFactory;
use App\Debug\GameDataCollector;
use App\Debug\TraceableGameEventApplier;
use App\Service\Game\Factory\GameContextFactory;
use App\Service\Game\GameEventApplier;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel implements CompilerPassInterface
{
    use MicroKernelTrait;

    public function process(ContainerBuilder $container): void
    {
        if ('dev' !== $this->getEnvironment()) {
            return;
        }

        $id = GameEventApplier::class;
        $definition = $container->getDefinition($id);
        $gameEventDataCollector = $container->getDefinition(GameDataCollector::class);

        $container
            ->register($traceableGameEventApplier = $id.'.traceable', TraceableGameEventApplier::class)
            ->setDecoratedService($id)
            ->setArguments([
                $definition,
                new Reference('debug.stopwatch'),
            ]);

        $id = GameContextFactory::class;
        $definition = $container->getDefinition($id);
        $container
            ->register($traceableGameContextFactory = $id.'.traceable', TraceableGameContextFactory::class)
            ->setDecoratedService($id)
            ->setArguments($definition->getArguments());

        $gameEventDataCollector->setArguments([
            new Reference($traceableGameEventApplier),
            new Reference($traceableGameContextFactory),
        ]);
    }
}
