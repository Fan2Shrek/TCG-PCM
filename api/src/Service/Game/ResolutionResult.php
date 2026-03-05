<?php

declare(strict_types=1);

namespace App\Service\Game;

use App\Game\State\GameEvent;
use App\Game\State\GameState;

final readonly class ResolutionResult
{
    /**
     * @param GameEvent[] $events
     */
    public function __construct(
        public array $events,
        public GameState $state,
    ) {}
}
