<?php

declare(strict_types=1);

namespace App\Service\Booster;

final class BoosterRegistry
{
    private const BOOSTER_TYPES = [
        'default' => Types\DefaultBooster::class,
        'character' => Types\CharacterBooster::class,
        'big' => Types\BigBooster::class,
        'isaac' => Types\IsaacBooster::class,
    ];

    public function getBoosterType(string $type): string
    {
        if (!array_key_exists($type, self::BOOSTER_TYPES)) {
            throw new \InvalidArgumentException(sprintf('Booster type "%s" is not registered.', $type));
        }

        return self::BOOSTER_TYPES[$type];
    }

    public function getAvailableBoosterTypes(): array
    {
        return array_keys(self::BOOSTER_TYPES);
    }
}
