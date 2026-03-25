<?php

declare(strict_types=1);

namespace App\DependencyInjection;

use App\Command\DeployCommand;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\Reference;

/*
 * Related to DeployCommand,
 *
 * Drunk too here :p
 */
final class DeployPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $services = $methods = [];

        foreach ($container->findTaggedServiceIds('app.deploy', true) as $id => $tags) {
            $services[$id] = new Reference($id);

            foreach ($tags as $attributes) {
                if (!($attributes['method'] ?? null)) {
                    throw new RuntimeException(\sprintf('Tag "kernel.reset" requires the "method" attribute to be set on service "%s".', $id));
                }

                if ($methods[$id] ?? null) {
                    $methods[$id] = [];
                }

                $methods[$id][] = $attributes['method'];
            }
        }

        $container->findDefinition(DeployCommand::class)->setArgument(0, new IteratorArgument($services))->setArgument(1, $methods);
    }
}
