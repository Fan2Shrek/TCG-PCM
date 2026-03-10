<?php

declare(strict_types=1);

namespace App\Game;

use App\Enum\CardEffectEnum;

final class GameUtils
{
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

                return sprintf('<%s>%s</%s>', $tag, self::formatValue($tag, $value), $tag);
            },
            $description,
        );

        return $result ?? throw new \LogicException('Failed to format description');
    }

    private static function formatValue(string $type, mixed $value): string
    {
        return match ($type) {
            'effect' => $value instanceof CardEffectEnum ? strtoupper($value->value) : (string) $value,
            default => (string) $value,
        };
    }
}
