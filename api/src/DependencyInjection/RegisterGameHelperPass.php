<?php

declare(strict_types=1);

namespace App\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class RegisterGameHelperPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $locator = $container->getDefinition('game.container');
        $services = $locator->getArgument(0);

        foreach ($container->findTaggedServiceIds('game.helper') as $id => $tags) {
            $services[$tags[0]['name'] ?? $id] = new Reference($id);
        }

        $locator->replaceArgument(0, $services);
    }
}
