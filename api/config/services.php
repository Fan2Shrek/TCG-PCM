<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Service\BadgeManager;
use App\Service\PublishEventMiddleware;
use App\Service\Redis\RedisConnection;
use App\Service\User\PasswordResetMailer;
use App\Tests\Resources\DoctrineCollector;
use App\Utils\KillSwitch;

return App::config([
    'parameters' => [
        'app.resources_dir' => "%kernel.project_dir%/resources",
        'app.cards_list' => "%app.resources_dir%/cards_list.php",
        'app.feature_list' => "%kernel.project_dir%/config/features.php",
    ],
    'services' => [
        '_defaults' => [
            'autowire' => true,
            'autoconfigure' => true,
        ],
        'App\\' => [
            'resource' => '../src/*',
            'exclude' => [
                '../src/Game',
                '../src/Debug',
                '../src/Service/Game',
            ],
        ],
        KillSwitch::class => [
            'arguments' => [
                '$featureList' => '%app.feature_list%',
            ],
        ],
        BadgeManager::class => [
            'arguments' => [
                '$badgeHandlers' => tagged_iterator('app.badge_handler'),
            ],
        ],
        RedisConnection::class => [
            'arguments' => [
                '$redisDsn' => '%env(REDIS_DSN)%',
            ],
        ],
        PasswordResetMailer::class => [
            'arguments' => [
                '$resetPasswordFrontUrl' => '%env(RESET_PASSWORD_FRONT_URL)%',
                '$mailerFrom' => '%env(MAILER_FROM)%',
            ],
        ],
        PublishEventMiddleware::class => [
            'tags' => [
                ['game.pipeline_middleware' => ['priority' => -100]]
            ]
        ],
    ],
    'when@test' => [
        'services' => [
            DoctrineCollector::class => [
                'autowire' => true,
                'autoconfigure' => true,
                'public' => true,
            ]
        ]
    ]
]);
