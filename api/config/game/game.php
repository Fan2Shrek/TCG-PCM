<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Service\EndGameHandler;
use App\Service\Game\CardFactory;
use App\Service\Game\CardFactoryInterface;
use App\Service\Game\CardRegistry;
use App\Service\Game\CardRegistryInterface;
use App\Service\Game\CardRuntimeMap;
use App\Service\Game\EndGameHandlerInterface;
use App\Service\Game\Factory\GameContextFactory;
use App\Service\Game\Factory\GameContextFactoryInterface;
use App\Service\Game\GameEventApplier;
use App\Service\Game\GameEventApplierInterface;
use App\Service\Game\GameEventResolver;
use App\Service\Game\GameInitializer;
use App\Service\Game\GameStateConverter;
use App\Service\Game\GameStateRebuilder;
use App\Service\Game\Pipeline\GamePipeline;
use App\Service\Game\Pipeline\Middleware\ConvertActionToEventMiddleware;
use App\Service\Game\Pipeline\Middleware\EndGameMiddleware;
use App\Service\Game\Pipeline\Middleware\ExceptionMiddleware;
use App\Service\Game\Pipeline\Middleware\ProvideGameStateMiddleware;
use App\Service\Game\Pipeline\Middleware\ResolveEventMiddleware;
use App\Service\Game\Pipeline\Middleware\SaveGameEventsMiddleware;
use App\Service\Game\Pipeline\Middleware\SaveGameStateMiddleware;
use App\Service\Game\Pipeline\Middleware\ValidateActionMiddleware;
use App\Service\Game\State\DoctrineGameEventRepository;
use App\Service\Game\State\DoctrineGameStateRepository;
use App\Service\Game\State\GameEventRepositoryInterface;
use App\Service\Game\State\GameStateProvider;
use App\Service\Game\State\GameStateRepositoryInterface;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set('game.event_resolver', GameEventResolver::class)
            ->args([
                service('game.card_runtime_map'),
                service('game.game_context_factory'),
                service('game.event_applier'),
            ])
        ->alias(GameEventResolver::class, 'game.event_resolver')

        ->set('game.initializer', GameInitializer::class)
        ->args([
            service('game.event_applier'),
            service('game.event_resolver'),
        ])
        ->alias(GameInitializer::class, 'game.initializer')

        ->set('game.card_runtime_map', CardRuntimeMap::class)
            ->args([
                service('game.card_factory')
            ])
            ->tag('kernel.reset', ['method' => 'clear'])

        ->set('game.game_context_factory', GameContextFactory::class)
        ->alias(GameContextFactoryInterface::class, 'game.game_context_factory')

        ->set('game.event_applier', GameEventApplier::class)
        ->alias(GameEventApplierInterface::class, 'game.event_applier')

        ->set('game.card_factory', CardFactory::class)
            ->args([
                service('game.card_registry'),
                service('cache.app'),
            ])
        ->alias(CardFactoryInterface::class, 'game.card_factory')

        ->set('game.game_state_converter', GameStateConverter::class)
            ->args([
                service('game.card_registry'),
            ])
        ->alias(GameStateConverter::class, 'game.game_state_converter')

        ->set('game.card_registry', CardRegistry::class)
            ->args([
                param('app.cards_list'),
            ])
        ->alias(CardRegistryInterface::class, 'game.card_registry')

        ->set('game.game_state_repository', DoctrineGameStateRepository::class)
            ->autowire(true)
        ->tag('app.deploy', ['method' => 'deleteAll'])
        ->alias(GameStateRepositoryInterface::class, 'game.game_state_repository')

        ->set('game.game_event_repository', DoctrineGameEventRepository::class)
            ->autowire(true)
        ->tag('app.deploy', ['method' => 'deleteAll'])
        ->alias(GameEventRepositoryInterface::class, 'game.game_event_repository')

        ->set('game.game_state_provider', GameStateProvider::class)
            ->args([
                service('game.game_state_repository'),
                service('game.game_event_repository'),
                service('game.game_state_rebuilder'),
            ])
        ->alias(GameStateProvider::class, 'game.game_state_provider')

        ->set('game.game_state_rebuilder', GameStateRebuilder::class)
            ->args([
                service('game.event_resolver'),
            ])
        ->alias(GameStateRebuilder::class, 'game.game_state_rebuilder')

        ->set('game.pipeline', GamePipeline::class)
            ->args([
                tagged_iterator('game.pipeline_middleware'),
            ])
        ->alias(GamePipeline::class, 'game.pipeline')

        ->set('game.end_game_handler', EndGameHandlerInterface::class)
            ->abstract(true)
            ->alias(EndGameHandlerInterface::class, 'game.end_game_handler')

        // Middlewares
        ->set('game.pipeline.middleware.exception', ExceptionMiddleware::class)
            ->tag('game.pipeline_middleware', ['priority' => 900])
            ->tag('monolog.logger', ['channel' => 'game'])
            ->call('setLogger', [service('logger')->ignoreOnInvalid()])

        ->set('game.pipeline.middleware.provide_game_state', ProvideGameStateMiddleware::class)
            ->tag('game.pipeline_middleware', ['priority' => 500])
            ->args([
                service('game.game_state_provider'),
            ])

        ->set('game.pipeline.middleware.validate_action', ValidateActionMiddleware::class)
            ->tag('game.pipeline_middleware', ['priority' => 300])

        ->set('game.pipeline.middleware.convert_action', ConvertActionToEventMiddleware::class)
            ->tag('game.pipeline_middleware', ['priority' => 150])

        ->set('game.pipeline.middleware.resolve_event', ResolveEventMiddleware::class)
            ->tag('game.pipeline_middleware', ['priority' => 0])
            ->args([
                service('game.event_resolver'),
            ])

        ->set('game.pipeline.middleware.save_events', SaveGameEventsMiddleware::class)
            ->tag('game.pipeline_middleware', ['priority' => -10])
            ->args([
                service('game.game_event_repository'),
            ])
        ->set('game.pipeline.middleware.save_state', SaveGameStateMiddleware::class)
            ->tag('game.pipeline_middleware', ['priority' => -100])
            ->args([
                service('game.game_state_repository'),
            ])
        ->set('game.pipeline.middleware.end_game', EndGameMiddleware::class)
            ->tag('game.pipeline_middleware', ['priority' => -150])
            ->args([
                service('game.end_game_handler'),
            ])
    ;
};
