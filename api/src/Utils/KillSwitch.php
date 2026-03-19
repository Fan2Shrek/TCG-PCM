<?php

declare(strict_types=1);

namespace App\Utils;

final class KillSwitch
{
    public const CREATE_ROOM = 'create_room';

    /**
     * @var array<string, bool>
     */
    private array $features = [];

    public function __construct(
        private string $featureList,
    ) {}

    public function isEnable(string $feature): bool
    {
        if ([] === $this->features) {
            $this->loadFeatures();
        }

        if (null === ($enable = $this->features[$feature] ?? null)) {
            throw new \InvalidArgumentException(\sprintf('Feature "%s" does not exist.', $feature));
        }

        return $enable;
    }

    private function loadFeatures(): void
    {
        try {
            $this->features = require $this->featureList;
        } catch (\Throwable $e) {
            // @mago-expect lint:no-empty-catch-clause we ignore this
        }
    }
}
