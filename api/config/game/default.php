<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Service\Game\CardFactory;
use App\Service\Game\CardFactoryInterface;
use App\Service\Game\CardRegistry;
use App\Service\Game\CardRegistryInterface;
use App\Service\Game\CardRuntimeMap;
use App\Service\Game\Factory\GameContextFactory;
use App\Service\Game\Factory\GameContextFactoryInterface;
use App\Service\Game\GameEventApplier;
use App\Service\Game\GameEventApplierInterface;
use App\Service\Game\GameEventResolver;
use App\Service\Game\GameInitializer;
use App\Service\Game\GameStateConverter;
use App\Service\Game\GameStateRebuilder;
use App\Service\Game\State\DoctrineGameEventRepository;
use App\Service\Game\State\DoctrineGameStateRepository;
use App\Service\Game\State\GameEventRepositoryInterface;
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
        ->alias(GameStateRepositoryInterface::class, 'game.game_state_repository')

        ->set('game.game_event_repository', DoctrineGameEventRepository::class)
            ->autowire(true)
        ->alias(GameEventRepositoryInterface::class, 'game.game_event_repository')

        ->set('game.game_state_rebuilder', GameStateRebuilder::class)
            ->args([
                service('game.event_resolver'),
            ])
        ->alias(GameStateRebuilder::class, 'game.game_state_rebuilder')
    ;
};
