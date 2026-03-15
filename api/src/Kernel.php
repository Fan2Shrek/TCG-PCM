<?php

namespace App;

use App\DependencyInjection\UseRedisGameStateRepositoryPass;
use App\Game\Badge\Handler\BadgeHandlerInterface;
use App\Tests\Resources\MockCardRegistry;
use App\Tests\Resources\MockHub;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Mercure\HubInterface;

class Kernel extends BaseKernel
{
    use MicroKernelTrait {
        configureContainer as private defaultConfigureContainer;
    }

    public function configureContainer(ContainerConfigurator $container, LoaderInterface $loader, ContainerBuilder $builder): void
    {
        $this->defaultConfigureContainer($container, $loader, $builder);
        $gameConfigDir = $this->getConfigDir().'/game';

        $loader->load($gameConfigDir.'/default.php');

        if ($builder->hasParameter('kernel.debug') && $builder->getParameter('kernel.debug')) {
            $loader->load($gameConfigDir.'/debug.php');
        }

        if ('test' === $container->env()) {
            $builder->register('game.card_registry', MockCardRegistry::class);
            $builder->register(HubInterface::class, MockHub::class);
        }
    }

    public function build(ContainerBuilder $container): void
    {
        $container->registerForAutoconfiguration(BadgeHandlerInterface::class)->addTag('app.badge_handler');

        $container->addCompilerPass(new UseRedisGameStateRepositoryPass());
    }
}
