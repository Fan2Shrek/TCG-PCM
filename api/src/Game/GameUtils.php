<?php

declare(strict_types=1);

namespace App\Game;

use App\Enum\CardEffectEnum;
use Psr\Container\ContainerInterface;

final class GameUtils
{
    private static ContainerInterface $container;

    public const array MARKUPS = [
        'effect',
        'value',
        'const',
    ];

    public static function formatDescription(string $description, array $data = []): string
    {
        $result = preg_replace_callback(
            '/{{([a-zA-Z_]+[0-9]*)}}/',
            static function ($match) use ($data) {
                $key = $match[1];

                if (!array_key_exists($key, $data)) {
                    return $match[0];
                }

                $value = $data[$key];

                $baseMatch = [];
                // extract base markup name (remove trailing digits)
                preg_match('/^([a-zA-Z_]+)/', $key, $baseMatch);
                $tag = $baseMatch[1];

                return \sprintf('<%s>%s</%s>', $tag, self::formatValue($tag, $value), $tag);
            },
            $description,
        );

        return $result ?? throw new \LogicException('Failed to format description');
    }

    public static function t(string $msg): string
    {
        return self::getService('translator')->trans($msg, [], 'game');
    }

    public static function setContainer(ContainerInterface $container): void
    {
        self::$container = $container;
    }

    public static function getService(string $id): mixed
    {
        if (!self::$container->has($id)) {
            throw new \LogicException(sprintf('Service with id "%s" not found in container.', $id));
        }

        return self::$container->get($id);
    }

    private static function formatValue(string $type, mixed $value): string
    {
        return match ($type) {
            'effect' => $value instanceof CardEffectEnum ? self::t(\sprintf('effects.%s.name', $value->value)) : (string) $value,
            default => (string) $value,
        };
    }
}
