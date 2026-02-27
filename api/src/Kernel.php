<?php

namespace App;

use App\Debug\TraceableGameEventApplier;
use App\Service\Game\GameEventApplierInterface;
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
        $id = GameEventApplierInterface::class;
        if ('dev' !== $this->getEnvironment() || !$container->hasDefinition($id)) {
            return;
        }

        $definition = $container->getDefinition($id);

        $container
            ->register($id.'.traceable', TraceableGameEventApplier::class)
            ->setDecoratedService($id)
            ->setArguments([
                $definition,
                new Reference('debug.stopwatch'),
            ]);
    }
}
