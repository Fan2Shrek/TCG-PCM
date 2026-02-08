<?php

declare(strict_types=1);

namespace App\Game\State;

use App\Enum\GameEventTypeEnum;

// @reflection this maybe should be an entity
// Or an detach entity with ::fromGameEvent()
final readonly class GameEvent
{
    public function __construct(
        public int $id,
        public GameEventTypeEnum $type,
        public array $data,
    ) {}
}
