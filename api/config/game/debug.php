<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Debug\Card\TraceableCardFactory;
use App\Debug\GameContext\TraceableGameContextFactory;
use App\Debug\GameDataCollector;
use App\Debug\TraceableGameEventApplier;
use App\Debug\TraceableGameMiddleware;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set('debug.game.event_applier', TraceableGameEventApplier::class)
            ->decorate('game.event_applier')
            ->args([
                service('debug.game.event_applier.inner'),
                service('debug.stopwatch'),
            ])
        ->set('debug.game.game_context_factory', TraceableGameContextFactory::class)
            ->decorate('game.game_context_factory')
        ->set('debug.game.card_factory', TraceableCardFactory::class)
            ->decorate('game.card_factory')
            ->args([
                service('debug.game.card_factory.inner'),
                service('debug.stopwatch'),
            ])
        ->set('data_collector.game', GameDataCollector::class)
            ->args([
                service('debug.game.event_applier'),
                service('debug.game.game_context_factory'),
                service('debug.game.card_factory'),
            ])
        ->tag('data_collector', [
            'template' => 'debug/game_events.html.twig',
            'id' => 'game',
            'priority' => 25,
        ])
        ->set('debug.game.pipeline.middleware_trace', TraceableGameMiddleware::class)
            ->tag('game.pipeline_middleware', ['priority' => 1000])
        ->args([
            service('debug.stopwatch')
        ])
    ;
};
